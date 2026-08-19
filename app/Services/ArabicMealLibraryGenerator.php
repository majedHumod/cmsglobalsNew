<?php

namespace App\Services;

/**
 * Generates ~300 healthy Gulf/Arabic meal recipes with USDA ingredient keys.
 */
class ArabicMealLibraryGenerator
{
    public function __construct(private UsdaNutritionCalculator $calculator)
    {
    }

    /**
     * @return list<array>
     */
    public function generate(int $count = 300): array
    {
        $recipes = array_merge(
            $this->classicRecipes(),
            $this->breakfastCombinations(),
            $this->lunchCombinations(),
            $this->dinnerCombinations(),
            $this->snackCombinations()
        );

        $unique = [];
        foreach ($recipes as $recipe) {
            $unique[$recipe['external_id']] = $recipe;
        }

        $meals = [];
        $i = 0;
        foreach (array_values($unique) as $recipe) {
            if ($i >= $count) {
                break;
            }
            $meals[] = $this->hydrate($recipe, $i + 1);
            $i++;
        }

        // Pad with more variations if still short.
        $pad = 1;
        while (count($meals) < $count) {
            $extra = $this->padRecipe($pad);
            $meals[] = $this->hydrate($extra, count($meals) + 1);
            $pad++;
        }

        return array_slice($meals, 0, $count);
    }

    /**
     * @param  array<string, mixed>  $recipe
     * @return array<string, mixed>
     */
    private function hydrate(array $recipe, int $seq): array
    {
        $nutrition = $this->calculator->calculate($recipe['ingredients']);
        $keys = array_column($recipe['ingredients'], 'key');
        $instructions = $this->buildInstructions(
            $recipe['meal_type'],
            $recipe['name_ar'],
            $recipe['name_en'],
            $keys,
            $recipe['ingredients']
        );

        return [
            'external_id' => $recipe['external_id'] ?? sprintf('ar-meal-%03d', $seq),
            'name' => $recipe['name_ar'],
            'name_en' => $recipe['name_en'],
            'description' => $recipe['description_ar'] ?? ('وجبة عربية/خليجية صحية: '.$recipe['name_ar']),
            'description_en' => $recipe['description_en'] ?? ('Healthy Gulf/Arabic meal: '.$recipe['name_en']),
            'meal_type' => $recipe['meal_type'],
            'difficulty' => $recipe['difficulty'] ?? 'easy',
            'prep_time' => $recipe['prep_time'] ?? 15,
            'cook_time' => $recipe['cook_time'] ?? 20,
            'servings' => $recipe['servings'] ?? 1,
            'instructions' => $instructions['ar'],
            'instructions_en' => $instructions['en'],
            'ingredients' => implode("\n", $nutrition['lines_ar']),
            'ingredients_en' => implode("\n", $nutrition['lines_en']),
            'ingredients_json' => $nutrition['breakdown'],
            'ingredient_keys' => $keys,
            'calories' => $nutrition['calories'],
            'protein' => $nutrition['protein'],
            'carbs' => $nutrition['carbs'],
            'fats' => $nutrition['fats'],
            'nutrition_is_estimated' => true,
            'nutrition_source' => config('meal_library.nutrition_source_label'),
            'is_active' => true,
            'audience_gender' => 'all',
        ];
    }

    /**
     * @param  list<string>  $keys
     * @param  list<array{key: string, grams: float|int}>  $ingredients
     * @return array{ar: string, en: string}
     */
    private function buildInstructions(string $mealType, string $nameAr, string $nameEn, array $keys, array $ingredients): array
    {
        $catalog = config('usda_ingredients', []);
        $linesAr = [];
        $linesEn = [];
        foreach ($ingredients as $row) {
            $item = $catalog[$row['key']] ?? null;
            if (! $item) {
                continue;
            }
            $g = rtrim(rtrim(number_format((float) $row['grams'], 1, '.', ''), '0'), '.');
            $linesAr[] = $item['name_ar'].' ('.$g.'غ)';
            $linesEn[] = $item['name_en'].' ('.$g.'g)';
        }

        $method = $this->detectMethod($keys, $nameEn, $nameAr, $mealType);
        $stepsAr = [];
        $stepsEn = [];

        $stepsAr[] = 'حضّر المكونات التالية لـ«'.$nameAr.'»: '.implode('، ', $linesAr).'.';
        $stepsEn[] = 'Prep ingredients for “'.$nameEn.'”: '.implode(', ', $linesEn).'.';

        foreach ($method['ar'] as $step) {
            $stepsAr[] = $step;
        }
        foreach ($method['en'] as $step) {
            $stepsEn[] = $step;
        }

        if ($this->shouldAddGarnishClose($mealType, $keys, $nameAr, $nameEn)) {
            $stepsAr[] = 'قدّم الحصة فورًا، ويمكن تزيينها بليمون أو أعشاب طازجة حسب الرغبة.';
            $stepsEn[] = 'Serve immediately; garnish with lemon or fresh herbs if desired.';
        } elseif ($mealType === 'snack') {
            $stepsAr[] = 'قدّم الوجبة الخفيفة مباشرة بعد التحضير.';
            $stepsEn[] = 'Serve the snack right after preparing it.';
        }

        return [
            'ar' => collect($stepsAr)->values()->map(fn ($s, $i) => ($i + 1).'. '.$s)->implode("\n"),
            'en' => collect($stepsEn)->values()->map(fn ($s, $i) => ($i + 1).'. '.$s)->implode("\n"),
        ];
    }

    /**
     * @param  list<string>  $keys
     */
    private function shouldAddGarnishClose(string $mealType, array $keys, string $nameAr, string $nameEn): bool
    {
        if ($mealType === 'snack') {
            return false;
        }

        $blob = mb_strtolower($nameAr.' '.$nameEn.' '.implode(' ', $keys));
        $noGarnish = [
            'dates', 'apple', 'banana', 'orange', 'raisins', 'almonds', 'walnuts', 'honey',
            'تمر', 'تفاح', 'موز', 'برتقال', 'زبيب', 'لوز', 'جوز', 'عسل',
            'oats', 'yogurt', 'labneh', 'شوفان', 'زبادي', 'لبنة',
        ];

        foreach ($noGarnish as $token) {
            if (str_contains($blob, $token)) {
                // Hot cooked mains that merely include yogurt sauce still get garnish.
                if (in_array($token, ['yogurt', 'labneh', 'زبادي', 'لبنة'], true)
                    && preg_match('/chicken|turkey|beef|fish|shrimp|دجاج|لحم|سمك|روبيان|ديك رومي/u', $blob)) {
                    continue;
                }

                return false;
            }
        }

        return true;
    }

    /**
     * @param  list<string>  $keys
     * @return array{ar: list<string>, en: list<string>}
     */
    private function detectMethod(array $keys, string $nameEn, string $nameAr, string $mealType): array
    {
        $blob = strtolower($nameEn.' '.$nameAr.' '.implode(' ', $keys));
        $keySet = array_fill_keys($keys, true);

        // Proteins first — never let a yogurt/salad side override the main dish.
        if (isset($keySet['shrimp_cooked']) || str_contains($blob, 'shrimp') || str_contains($nameAr, 'روبيان') || str_contains($nameAr, 'ربيان')) {
            return [
                'ar' => [
                    'نظّف الروبيان وتبّله بالثوم والليمون ورشة بهارات خفيفة.',
                    'اشوه أو اقليه بسرعة في مقلاة 3–5 دقائق حتى يتحوّل للون الوردي دون إطالة الطهي.',
                    'حضّر الجانب (أرز/سلطة/خضار) وقدّم الروبيان فوقه ساخنًا.',
                ],
                'en' => [
                    'Clean shrimp and season with garlic, lemon, and light spices.',
                    'Grill or pan-sear 3–5 minutes until pink; do not overcook.',
                    'Prepare the side (rice/salad/veg) and serve shrimp on top while hot.',
                ],
            ];
        }

        if (str_contains($blob, 'salmon') || str_contains($blob, 'fish') || str_contains($blob, 'tuna')
            || str_contains($nameAr, 'سمك') || str_contains($nameAr, 'سلمون') || str_contains($nameAr, 'تونة')) {
            return [
                'ar' => [
                    'تبّل السمك بالليمون والثوم وملح خفيف.',
                    'اشوه في الفرن أو على الشواية حتى ينضج ويتفتت بسهولة بالشوكة.',
                    'قدّمه مع الجانب النباتي أو الحبوب المدرجة في المكونات.',
                ],
                'en' => [
                    'Season fish with lemon, garlic, and light salt.',
                    'Bake or grill until it flakes easily with a fork.',
                    'Serve with the listed vegetable or grain side.',
                ],
            ];
        }

        if (isset($keySet['chicken_breast_cooked']) || isset($keySet['chicken_thigh_cooked'])
            || str_contains($blob, 'chicken') || str_contains($nameAr, 'دجاج') || str_contains($nameAr, 'طاووق')) {
            return [
                'ar' => [
                    'تبّل قطع الدجاج بالبهارات الخليجية الخفيفة وزيت الزيتون.',
                    'اشوها أو اطهها على نار متوسطة حتى ينضج الداخل تمامًا دون حرق السطح.',
                    'جهّز الأرز/الخضار/السلطة ثم اجمع الطبق وقدّمه ساخنًا.',
                ],
                'en' => [
                    'Season chicken with light Gulf spices and olive oil.',
                    'Grill or pan-cook over medium heat until fully cooked through.',
                    'Prepare rice/veg/salad, plate together, and serve hot.',
                ],
            ];
        }

        if (isset($keySet['turkey_breast']) || str_contains($blob, 'turkey') || str_contains($nameAr, 'ديك رومي')) {
            return [
                'ar' => [
                    'تبّل صدر الديك الرومي ببهارات خفيفة وزيت الزيتون.',
                    'اشوه أو اقله على نار متوسطة حتى ينضج تمامًا ويبقى طريًا.',
                    'جهّز الخضار أو الجانب المرافق ثم قدّم الطبق ساخنًا.',
                ],
                'en' => [
                    'Season turkey breast with light spices and olive oil.',
                    'Grill or pan-cook over medium heat until fully done and tender.',
                    'Prepare the accompanying vegetables/side and serve hot.',
                ],
            ];
        }

        if (str_contains($blob, 'beef') || str_contains($blob, 'lamb') || str_contains($nameAr, 'لحم') || str_contains($nameAr, 'كفتة')) {
            return [
                'ar' => [
                    'شكّل اللحم أو قطّعه حسب الوصفة، وتبّله ببهارات خفيفة.',
                    'اشوه أو اطهه حتى ينضج مع تقليل الدهون الزائدة.',
                    'قدّمه مع الحبوب أو الخضار المرافقة في المكونات.',
                ],
                'en' => [
                    'Shape or cut the meat as needed and season lightly.',
                    'Grill or cook through while draining excess fat.',
                    'Serve with the accompanying grains or vegetables.',
                ],
            ];
        }

        if (isset($keySet['oats_dry']) || str_contains($blob, 'oats') || str_contains($nameAr, 'شوفان')) {
            return [
                'ar' => [
                    'اسكب الحليب على الشوفان وحرّك حتى يتماسك القوام على نار هادئة أو اتركه منقوعًا.',
                    'أضف الإضافات (تمر/فاكهة/مكسرات) في النهاية دون طهي طويل للحفاظ على القرمشة.',
                ],
                'en' => [
                    'Pour milk over oats and stir on low heat until creamy, or soak until soft.',
                    'Add toppings (dates/fruit/nuts) at the end to keep texture.',
                ],
            ];
        }

        if (isset($keySet['egg_whole']) || isset($keySet['egg_white']) || str_contains($nameAr, 'بيض') || str_contains($nameAr, 'عجة')) {
            return [
                'ar' => [
                    'اخفق البيض مع رشة ملح، ثم اطهه في مقلاة غير لاصقة بكمية قليلة من الزيت.',
                    'أضف الخضار سريعًا في آخر دقيقة حتى تبقى طازجة، ثم قدّم مع الخبز إن وُجد.',
                ],
                'en' => [
                    'Whisk eggs with a pinch of salt, then cook in a nonstick pan with a little oil.',
                    'Fold in vegetables in the last minute, then serve with bread if included.',
                ],
            ];
        }

        if (isset($keySet['greek_yogurt_nonfat']) || isset($keySet['labneh']) || isset($keySet['cottage_cheese_lowfat'])
            || str_contains($nameAr, 'زبادي') || str_contains($nameAr, 'لبنة') || str_contains($nameAr, 'قريش')) {
            return [
                'ar' => [
                    'ضع الأساس اللبني في وعاء التقديم وبرّده إن لزم.',
                    'رتّب الإضافات فوقه (فاكهة/خضار/مكسرات) وقدّمه باردًا.',
                ],
                'en' => [
                    'Spoon the dairy base into a serving bowl and chill if needed.',
                    'Arrange toppings (fruit/veg/nuts) on top and serve cold.',
                ],
            ];
        }

        if (str_contains($blob, 'lentil') || str_contains($blob, 'soup') || str_contains($nameAr, 'عدس') || str_contains($nameAr, 'شوربة') || str_contains($nameAr, 'حريرة')) {
            return [
                'ar' => [
                    'حمّر البصل والثوم قليلًا ثم أضف البقوليات/الخضار والمرق.',
                    'اترك المزيج يغلي على نار هادئة حتى يطرى القوام.',
                    'عدّل التوابل بالليمون والملح وقدّم ساخنًا.',
                ],
                'en' => [
                    'Sauté onion and garlic briefly, then add legumes/vegetables and broth.',
                    'Simmer gently until tender and slightly thickened.',
                    'Adjust seasoning with lemon and salt, then serve hot.',
                ],
            ];
        }

        if (str_contains($blob, 'okra') || str_contains($blob, 'stew') || str_contains($nameAr, 'بامية') || str_contains($nameAr, 'فاصوليا')) {
            return [
                'ar' => [
                    'اطبخ البصل وصلصة الطماطم حتى تتجانس.',
                    'أضف الخضار الأساسية والبروتين إن وُجد، واتركها تنضج على نار هادئة.',
                    'قدّم الطبق مع الأرز إن كان ضمن المكونات.',
                ],
                'en' => [
                    'Cook onion with tomato sauce until combined.',
                    'Add main vegetables and protein if included; simmer until tender.',
                    'Serve with rice when listed in the ingredients.',
                ],
            ];
        }

        if (str_contains($blob, 'rice') || str_contains($blob, 'kabsa') || str_contains($blob, 'machboos') || str_contains($blob, 'mandi')
            || str_contains($nameAr, 'أرز') || str_contains($nameAr, 'كبسة') || str_contains($nameAr, 'مجبوس') || str_contains($nameAr, 'مندي') || str_contains($nameAr, 'مقلوبة')) {
            return [
                'ar' => [
                    'حضّر البروتين والخضار أولًا بالشوي أو التحمير الخفيف.',
                    'اطبخ الأرز/الحبوب بالتوابل حتى تنضج الحبة.',
                    'اجمع الطبقات وقدّم الطبق ساخنًا بكمية الحصة المحددة.',
                ],
                'en' => [
                    'Cook protein and vegetables first by grilling or light sautéing.',
                    'Cook the rice/grains with spices until tender.',
                    'Layer and serve hot as a single portion.',
                ],
            ];
        }

        if (str_contains($blob, 'hummus') || str_contains($blob, 'fava') || str_contains($nameAr, 'فول') || str_contains($nameAr, 'حمص')) {
            return [
                'ar' => [
                    'سخّن أو اهرس المكوّن الأساسي حسب الحاجة مع الليمون والثوم.',
                    'رتّب الخضار أو الخبز بجانبه وقدّم فوراً.',
                ],
                'en' => [
                    'Warm or mash the base with lemon and garlic as needed.',
                    'Arrange vegetables or bread beside it and serve promptly.',
                ],
            ];
        }

        if ($mealType === 'snack' || str_contains($blob, 'date') || str_contains($blob, 'apple') || str_contains($blob, 'banana')) {
            return [
                'ar' => [
                    'اغسل المكوّنات الطازجة وقطّعها إن لزم.',
                    'اجمعها في وعاء صغير وقدّمها مباشرة دون طهي.',
                ],
                'en' => [
                    'Wash fresh items and cut if needed.',
                    'Combine in a small bowl and serve with no cooking required.',
                ],
            ];
        }

        if (str_contains($blob, 'salad') || str_contains($nameAr, 'سلطة') || str_contains($nameAr, 'تبولة') || str_contains($nameAr, 'فتوش')) {
            return [
                'ar' => [
                    'اغسل الخضار وقطّعها قطعًا متناسقة.',
                    'اخلطها مع زيت الزيتون والليمون قبل التقديم مباشرة.',
                ],
                'en' => [
                    'Wash and chop vegetables evenly.',
                    'Toss with olive oil and lemon just before serving.',
                ],
            ];
        }

        return [
            'ar' => [
                'اطبخ المكونات الأساسية بطريقة صحية مناسبة (شوي، سلق، أو طهي خفيف).',
                'اجمع المكونات في طبق واحد وفق ترتيب الوصفة.',
            ],
            'en' => [
                'Cook the main ingredients with a healthy method (grill, boil, or light simmer).',
                'Assemble everything on one plate following the recipe order.',
            ],
        ];
    }

    /**
     * @return list<array>
     */
    private function classicRecipes(): array
    {
        return [
            $this->r('classic-001', 'breakfast', 'شوفان بالتمر واللوز', 'Oats with dates and almonds', [
                ['key' => 'oats_dry', 'grams' => 50], ['key' => 'milk_lowfat', 'grams' => 200], ['key' => 'dates', 'grams' => 20], ['key' => 'almonds', 'grams' => 10],
            ], 10, 5, 'easy'),
            $this->r('classic-002', 'breakfast', 'لبنة بالخيار وخبز أسمر', 'Labneh with cucumber and whole wheat pita', [
                ['key' => 'labneh', 'grams' => 80], ['key' => 'cucumber', 'grams' => 100], ['key' => 'pita_whole_wheat', 'grams' => 40], ['key' => 'olive_oil', 'grams' => 5], ['key' => 'mint', 'grams' => 5],
            ], 10, 0, 'easy'),
            $this->r('classic-003', 'breakfast', 'بيض مسلوق مع خضار', 'Boiled eggs with vegetables', [
                ['key' => 'egg_whole', 'grams' => 100], ['key' => 'tomato', 'grams' => 100], ['key' => 'cucumber', 'grams' => 100], ['key' => 'whole_wheat_bread', 'grams' => 30],
            ], 10, 10, 'easy'),
            $this->r('classic-004', 'breakfast', 'فول مدمس خفيف', 'Light foul medames', [
                ['key' => 'fava_beans_cooked', 'grams' => 180], ['key' => 'olive_oil', 'grams' => 8], ['key' => 'lemon_juice', 'grams' => 15], ['key' => 'tomato', 'grams' => 80], ['key' => 'pita_whole_wheat', 'grams' => 40],
            ], 10, 15, 'easy'),
            $this->r('classic-005', 'breakfast', 'زبادي يوناني بالعسل والموز', 'Greek yogurt with honey and banana', [
                ['key' => 'greek_yogurt_nonfat', 'grams' => 170], ['key' => 'banana', 'grams' => 80], ['key' => 'honey', 'grams' => 10], ['key' => 'walnuts', 'grams' => 10],
            ], 5, 0, 'easy'),
            $this->r('classic-006', 'lunch', 'كبسة دجاج خفيفة بالأرز البني', 'Light chicken kabsa with brown rice', [
                ['key' => 'chicken_breast_cooked', 'grams' => 140], ['key' => 'rice_brown_cooked', 'grams' => 150], ['key' => 'tomato', 'grams' => 80], ['key' => 'onion', 'grams' => 40], ['key' => 'carrot', 'grams' => 40], ['key' => 'olive_oil', 'grams' => 8],
            ], 20, 35, 'medium'),
            $this->r('classic-007', 'lunch', 'مجبوس روبيان صحي', 'Healthy shrimp machboos', [
                ['key' => 'shrimp_cooked', 'grams' => 140], ['key' => 'rice_brown_cooked', 'grams' => 140], ['key' => 'tomato', 'grams' => 70], ['key' => 'onion', 'grams' => 40], ['key' => 'bell_pepper', 'grams' => 50], ['key' => 'olive_oil', 'grams' => 7],
            ], 20, 30, 'medium'),
            $this->r('classic-008', 'lunch', 'مندي دجاج مشوي خفيف', 'Light grilled chicken mandi', [
                ['key' => 'chicken_breast_cooked', 'grams' => 150], ['key' => 'rice_brown_cooked', 'grams' => 140], ['key' => 'onion', 'grams' => 30], ['key' => 'mixed_vegetables', 'grams' => 100], ['key' => 'olive_oil', 'grams' => 6],
            ], 15, 40, 'medium'),
            $this->r('classic-009', 'lunch', 'سمك مشوي مع سلطة وخضار', 'Grilled fish with salad and vegetables', [
                ['key' => 'fish_white_cooked', 'grams' => 160], ['key' => 'lettuce', 'grams' => 80], ['key' => 'tomato', 'grams' => 80], ['key' => 'cucumber', 'grams' => 80], ['key' => 'olive_oil', 'grams' => 8], ['key' => 'lemon_juice', 'grams' => 15],
            ], 15, 20, 'easy'),
            $this->r('classic-010', 'lunch', 'سلمون مشوي مع كينوا', 'Baked salmon with quinoa', [
                ['key' => 'salmon_cooked', 'grams' => 140], ['key' => 'quinoa_cooked', 'grams' => 140], ['key' => 'broccoli', 'grams' => 120], ['key' => 'lemon_juice', 'grams' => 10], ['key' => 'olive_oil', 'grams' => 5],
            ], 10, 25, 'easy'),
            $this->r('classic-011', 'lunch', 'مجبوس لحم قليل الدهن', 'Lean beef machboos', [
                ['key' => 'lean_beef_cooked', 'grams' => 120], ['key' => 'rice_brown_cooked', 'grams' => 140], ['key' => 'tomato', 'grams' => 70], ['key' => 'carrot', 'grams' => 50], ['key' => 'onion', 'grams' => 40], ['key' => 'olive_oil', 'grams' => 6],
            ], 20, 40, 'medium'),
            $this->r('classic-012', 'lunch', 'مقلوبة دجاج خفيفة', 'Light chicken maqluba', [
                ['key' => 'chicken_breast_cooked', 'grams' => 130], ['key' => 'rice_brown_cooked', 'grams' => 130], ['key' => 'eggplant', 'grams' => 100], ['key' => 'tomato', 'grams' => 60], ['key' => 'olive_oil', 'grams' => 6],
            ], 25, 40, 'medium'),
            $this->r('classic-013', 'lunch', 'عدس أحمر مع سلطة', 'Red lentil stew with salad', [
                ['key' => 'lentils_cooked', 'grams' => 200], ['key' => 'onion', 'grams' => 40], ['key' => 'carrot', 'grams' => 50], ['key' => 'lemon_juice', 'grams' => 15], ['key' => 'lettuce', 'grams' => 80], ['key' => 'cucumber', 'grams' => 80], ['key' => 'olive_oil', 'grams' => 5],
            ], 10, 25, 'easy'),
            $this->r('classic-014', 'lunch', 'حمص بالطحينة مع خضار', 'Hummus with fresh vegetables', [
                ['key' => 'hummus', 'grams' => 120], ['key' => 'carrot', 'grams' => 80], ['key' => 'cucumber', 'grams' => 100], ['key' => 'bell_pepper', 'grams' => 80], ['key' => 'pita_whole_wheat', 'grams' => 40],
            ], 10, 0, 'easy'),
            $this->r('classic-015', 'dinner', 'شيش طاووق مشوي مع سلطة', 'Grilled shish tawook with salad', [
                ['key' => 'chicken_breast_cooked', 'grams' => 160], ['key' => 'lettuce', 'grams' => 100], ['key' => 'tomato', 'grams' => 80], ['key' => 'cucumber', 'grams' => 80], ['key' => 'yogurt_lowfat', 'grams' => 40], ['key' => 'olive_oil', 'grams' => 5],
            ], 20, 20, 'easy'),
            $this->r('classic-016', 'dinner', 'كفتة مشوية مع برغل', 'Grilled kofta with bulgur', [
                ['key' => 'lean_beef_cooked', 'grams' => 130], ['key' => 'bulgur_cooked', 'grams' => 140], ['key' => 'parsley', 'grams' => 15], ['key' => 'onion', 'grams' => 30], ['key' => 'mixed_vegetables', 'grams' => 120], ['key' => 'olive_oil', 'grams' => 5],
            ], 20, 25, 'medium'),
            $this->r('classic-017', 'dinner', 'بامية بلحم قليل الدهن', 'Okra stew with lean beef', [
                ['key' => 'okras', 'grams' => 180], ['key' => 'lean_beef_cooked', 'grams' => 100], ['key' => 'tomato_sauce', 'grams' => 100], ['key' => 'onion', 'grams' => 40], ['key' => 'rice_brown_cooked', 'grams' => 100], ['key' => 'olive_oil', 'grams' => 6],
            ], 15, 35, 'medium'),
            $this->r('classic-018', 'dinner', 'كوسا محشي خفيف', 'Light stuffed zucchini', [
                ['key' => 'zucchini', 'grams' => 250], ['key' => 'lean_beef_cooked', 'grams' => 80], ['key' => 'rice_brown_cooked', 'grams' => 80], ['key' => 'tomato_sauce', 'grams' => 100], ['key' => 'onion', 'grams' => 30], ['key' => 'olive_oil', 'grams' => 5],
            ], 25, 40, 'hard'),
            $this->r('classic-019', 'dinner', 'دجاج مشوي مع بطاطا حلوة', 'Grilled chicken with sweet potato', [
                ['key' => 'chicken_breast_cooked', 'grams' => 150], ['key' => 'sweet_potato', 'grams' => 150], ['key' => 'broccoli', 'grams' => 120], ['key' => 'olive_oil', 'grams' => 6],
            ], 10, 30, 'easy'),
            $this->r('classic-020', 'dinner', 'شوربة حريرة خفيفة', 'Light harira soup', [
                ['key' => 'lentils_cooked', 'grams' => 100], ['key' => 'chickpeas_cooked', 'grams' => 80], ['key' => 'tomato', 'grams' => 100], ['key' => 'onion', 'grams' => 40], ['key' => 'broth_chicken', 'grams' => 250], ['key' => 'parsley', 'grams' => 10], ['key' => 'olive_oil', 'grams' => 5],
            ], 15, 35, 'medium'),
            $this->r('classic-021', 'snack', 'تمر مع لوز', 'Dates with almonds', [
                ['key' => 'dates', 'grams' => 30], ['key' => 'almonds', 'grams' => 15],
            ], 2, 0, 'easy'),
            $this->r('classic-022', 'snack', 'تفاح مع زبادي', 'Apple with yogurt', [
                ['key' => 'apple', 'grams' => 150], ['key' => 'greek_yogurt_nonfat', 'grams' => 100],
            ], 3, 0, 'easy'),
            $this->r('classic-023', 'snack', 'حمص وخضار', 'Hummus and veggies snack', [
                ['key' => 'hummus', 'grams' => 60], ['key' => 'carrot', 'grams' => 80], ['key' => 'cucumber', 'grams' => 80],
            ], 5, 0, 'easy'),
            $this->r('classic-024', 'snack', 'برتقال ولوز', 'Orange and almonds', [
                ['key' => 'orange', 'grams' => 180], ['key' => 'almonds', 'grams' => 12],
            ], 2, 0, 'easy'),
            $this->r('classic-025', 'lunch', 'فتة دجاج خفيفة', 'Light chicken fatta', [
                ['key' => 'chicken_breast_cooked', 'grams' => 120], ['key' => 'yogurt_lowfat', 'grams' => 120], ['key' => 'pita_whole_wheat', 'grams' => 40], ['key' => 'chickpeas_cooked', 'grams' => 60], ['key' => 'garlic', 'grams' => 5], ['key' => 'olive_oil', 'grams' => 5],
            ], 15, 20, 'medium'),
            $this->r('classic-026', 'lunch', 'تبولة مع دجاج مشوي', 'Tabbouleh with grilled chicken', [
                ['key' => 'bulgur_cooked', 'grams' => 80], ['key' => 'parsley', 'grams' => 60], ['key' => 'tomato', 'grams' => 100], ['key' => 'cucumber', 'grams' => 80], ['key' => 'lemon_juice', 'grams' => 20], ['key' => 'olive_oil', 'grams' => 10], ['key' => 'chicken_breast_cooked', 'grams' => 120],
            ], 20, 15, 'easy'),
            $this->r('classic-027', 'lunch', 'فتوش مع مشاوي دجاج', 'Fattoush with grilled chicken', [
                ['key' => 'lettuce', 'grams' => 100], ['key' => 'tomato', 'grams' => 80], ['key' => 'cucumber', 'grams' => 80], ['key' => 'pita_whole_wheat', 'grams' => 30], ['key' => 'lemon_juice', 'grams' => 15], ['key' => 'olive_oil', 'grams' => 8], ['key' => 'chicken_breast_cooked', 'grams' => 130],
            ], 15, 15, 'easy'),
            $this->r('classic-028', 'dinner', 'صيادية سمك خفيفة', 'Light fish sayadieh', [
                ['key' => 'fish_white_cooked', 'grams' => 150], ['key' => 'rice_brown_cooked', 'grams' => 140], ['key' => 'onion', 'grams' => 50], ['key' => 'lemon_juice', 'grams' => 15], ['key' => 'olive_oil', 'grams' => 7],
            ], 15, 30, 'medium'),
            $this->r('classic-029', 'dinner', 'محشي ورق عنب خفيف (حصة)', 'Light stuffed grape leaves (portion)', [
                ['key' => 'rice_brown_cooked', 'grams' => 100], ['key' => 'lean_beef_cooked', 'grams' => 60], ['key' => 'parsley', 'grams' => 20], ['key' => 'tomato', 'grams' => 60], ['key' => 'lemon_juice', 'grams' => 20], ['key' => 'olive_oil', 'grams' => 8],
            ], 30, 45, 'hard'),
            $this->r('classic-030', 'breakfast', 'عجة خضار صحية', 'Healthy vegetable omelette', [
                ['key' => 'egg_whole', 'grams' => 100], ['key' => 'egg_white', 'grams' => 60], ['key' => 'spinach', 'grams' => 60], ['key' => 'tomato', 'grams' => 60], ['key' => 'mushroom', 'grams' => 50], ['key' => 'olive_oil', 'grams' => 5],
            ], 10, 10, 'easy'),
        ];
    }

    /**
     * @param  list<array{key: string, grams: float|int}>  $ingredients
     * @return array<string, mixed>
     */
    private function r(
        string $id,
        string $type,
        string $nameAr,
        string $nameEn,
        array $ingredients,
        int $prep,
        int $cook,
        string $difficulty
    ): array {
        $ingredients = array_values(array_filter($ingredients, fn ($i) => isset($i['key']) && isset($i['grams'])));

        return [
            'external_id' => $id,
            'meal_type' => $type,
            'name_ar' => $nameAr,
            'name_en' => $nameEn,
            'ingredients' => $ingredients,
            'prep_time' => $prep,
            'cook_time' => $cook,
            'difficulty' => $difficulty,
        ];
    }

    /** @return list<array> */
    private function breakfastCombinations(): array
    {
        $bases = [
            ['ar' => 'شوفان', 'en' => 'Oats bowl', 'items' => [['key' => 'oats_dry', 'grams' => 45], ['key' => 'milk_lowfat', 'grams' => 180]]],
            ['ar' => 'زبادي يوناني', 'en' => 'Greek yogurt bowl', 'items' => [['key' => 'greek_yogurt_nonfat', 'grams' => 170]]],
            ['ar' => 'بيض وخضار', 'en' => 'Eggs and veggies', 'items' => [['key' => 'egg_whole', 'grams' => 100], ['key' => 'tomato', 'grams' => 80]]],
            ['ar' => 'لبنة', 'en' => 'Labneh plate', 'items' => [['key' => 'labneh', 'grams' => 70], ['key' => 'cucumber', 'grams' => 80]]],
            ['ar' => 'فول', 'en' => 'Foul bowl', 'items' => [['key' => 'fava_beans_cooked', 'grams' => 160], ['key' => 'lemon_juice', 'grams' => 10]]],
            ['ar' => 'جبن قريش', 'en' => 'Cottage cheese plate', 'items' => [['key' => 'cottage_cheese_lowfat', 'grams' => 120], ['key' => 'tomato', 'grams' => 80]]],
        ];
        $addOns = [
            ['ar' => 'بالتمر', 'en' => 'with dates', 'items' => [['key' => 'dates', 'grams' => 20]]],
            ['ar' => 'بالموز', 'en' => 'with banana', 'items' => [['key' => 'banana', 'grams' => 80]]],
            ['ar' => 'بالتفاح', 'en' => 'with apple', 'items' => [['key' => 'apple', 'grams' => 100]]],
            ['ar' => 'باللوز', 'en' => 'with almonds', 'items' => [['key' => 'almonds', 'grams' => 12]]],
            ['ar' => 'بالعسل', 'en' => 'with honey', 'items' => [['key' => 'honey', 'grams' => 8]]],
            ['ar' => 'بخبز أسمر', 'en' => 'with whole wheat bread', 'items' => [['key' => 'whole_wheat_bread', 'grams' => 35]]],
            ['ar' => 'بزيت الزيتون', 'en' => 'with olive oil', 'items' => [['key' => 'olive_oil', 'grams' => 5]]],
            ['ar' => 'بالسبانخ', 'en' => 'with spinach', 'items' => [['key' => 'spinach', 'grams' => 60]]],
            ['ar' => 'بالأفوكادو', 'en' => 'with avocado', 'items' => [['key' => 'avocado', 'grams' => 40]]],
            ['ar' => 'بالبرتقال', 'en' => 'with orange', 'items' => [['key' => 'orange', 'grams' => 120]]],
        ];

        return $this->combine('bf', 'breakfast', $bases, $addOns, 70);
    }

    /** @return list<array> */
    private function lunchCombinations(): array
    {
        $bases = [
            ['ar' => 'صدر دجاج مشوي', 'en' => 'Grilled chicken breast', 'items' => [['key' => 'chicken_breast_cooked', 'grams' => 140]]],
            ['ar' => 'سمك مشوي', 'en' => 'Grilled white fish', 'items' => [['key' => 'fish_white_cooked', 'grams' => 150]]],
            ['ar' => 'روبيان', 'en' => 'Shrimp plate', 'items' => [['key' => 'shrimp_cooked', 'grams' => 140]]],
            ['ar' => 'لحم قليل الدهن', 'en' => 'Lean beef', 'items' => [['key' => 'lean_beef_cooked', 'grams' => 110]]],
            ['ar' => 'عدس', 'en' => 'Lentil bowl', 'items' => [['key' => 'lentils_cooked', 'grams' => 200]]],
            ['ar' => 'حمص مطبوخ', 'en' => 'Chickpea bowl', 'items' => [['key' => 'chickpeas_cooked', 'grams' => 180]]],
            ['ar' => 'تونة', 'en' => 'Tuna plate', 'items' => [['key' => 'tuna_canned_water', 'grams' => 120]]],
            ['ar' => 'ديك رومي', 'en' => 'Turkey breast', 'items' => [['key' => 'turkey_breast', 'grams' => 140]]],
        ];
        $addOns = [
            ['ar' => 'مع أرز بني', 'en' => 'with brown rice', 'items' => [['key' => 'rice_brown_cooked', 'grams' => 140], ['key' => 'mixed_vegetables', 'grams' => 100]]],
            ['ar' => 'مع برغل وسلطة', 'en' => 'with bulgur and salad', 'items' => [['key' => 'bulgur_cooked', 'grams' => 130], ['key' => 'lettuce', 'grams' => 80], ['key' => 'tomato', 'grams' => 70]]],
            ['ar' => 'مع كينوا وخضار', 'en' => 'with quinoa and veggies', 'items' => [['key' => 'quinoa_cooked', 'grams' => 130], ['key' => 'broccoli', 'grams' => 100]]],
            ['ar' => 'مع فريكة', 'en' => 'with freekeh', 'items' => [['key' => 'freekeh_cooked', 'grams' => 140], ['key' => 'onion', 'grams' => 30]]],
            ['ar' => 'مع سلطة خليجية', 'en' => 'with Gulf salad', 'items' => [['key' => 'cucumber', 'grams' => 100], ['key' => 'tomato', 'grams' => 100], ['key' => 'lettuce', 'grams' => 80], ['key' => 'olive_oil', 'grams' => 7], ['key' => 'lemon_juice', 'grams' => 10]]],
            ['ar' => 'مع بطاطا حلوة', 'en' => 'with sweet potato', 'items' => [['key' => 'sweet_potato', 'grams' => 150], ['key' => 'green_beans', 'grams' => 100]]],
            ['ar' => 'مع حمص وسلطة', 'en' => 'with hummus and salad', 'items' => [['key' => 'hummus', 'grams' => 70], ['key' => 'cucumber', 'grams' => 80], ['key' => 'carrot', 'grams' => 60]]],
            ['ar' => 'ستايل مجبوس خفيف', 'en' => 'light machboos style', 'items' => [['key' => 'rice_brown_cooked', 'grams' => 130], ['key' => 'tomato', 'grams' => 60], ['key' => 'carrot', 'grams' => 40], ['key' => 'onion', 'grams' => 30], ['key' => 'olive_oil', 'grams' => 6]]],
            ['ar' => 'مع شوربة عدس جانبية', 'en' => 'with side lentil soup', 'items' => [['key' => 'lentils_cooked', 'grams' => 100], ['key' => 'broth_chicken', 'grams' => 150], ['key' => 'pita_whole_wheat', 'grams' => 30]]],
            ['ar' => 'مع باذنجان مشوي', 'en' => 'with grilled eggplant', 'items' => [['key' => 'eggplant', 'grams' => 150], ['key' => 'yogurt_lowfat', 'grams' => 60], ['key' => 'olive_oil', 'grams' => 5]]],
        ];

        return $this->combine('ln', 'lunch', $bases, $addOns, 90);
    }

    /** @return list<array> */
    private function dinnerCombinations(): array
    {
        $bases = [
            ['ar' => 'دجاج مشوي عشاء', 'en' => 'Dinner grilled chicken', 'items' => [['key' => 'chicken_breast_cooked', 'grams' => 150]]],
            ['ar' => 'سمك فرن', 'en' => 'Baked fish dinner', 'items' => [['key' => 'fish_white_cooked', 'grams' => 160]]],
            ['ar' => 'سلمون', 'en' => 'Salmon dinner', 'items' => [['key' => 'salmon_cooked', 'grams' => 130]]],
            ['ar' => 'كفتة مشوية', 'en' => 'Grilled kofta dinner', 'items' => [['key' => 'lean_beef_cooked', 'grams' => 120]]],
            ['ar' => 'بامية نباتية', 'en' => 'Vegetarian okra', 'items' => [['key' => 'okras', 'grams' => 200], ['key' => 'tomato_sauce', 'grams' => 100]]],
            ['ar' => 'فاصوليا خضراء', 'en' => 'Green beans stew', 'items' => [['key' => 'green_beans', 'grams' => 200], ['key' => 'tomato_sauce', 'grams' => 80]]],
            ['ar' => 'ديك رومي مشوي', 'en' => 'Grilled turkey', 'items' => [['key' => 'turkey_breast', 'grams' => 150]]],
        ];
        $addOns = [
            ['ar' => 'مع سلطة كبيرة', 'en' => 'with large salad', 'items' => [['key' => 'lettuce', 'grams' => 120], ['key' => 'tomato', 'grams' => 80], ['key' => 'cucumber', 'grams' => 80], ['key' => 'olive_oil', 'grams' => 8]]],
            ['ar' => 'مع أرز بني صغير', 'en' => 'with small brown rice', 'items' => [['key' => 'rice_brown_cooked', 'grams' => 100], ['key' => 'mixed_vegetables', 'grams' => 120]]],
            ['ar' => 'مع بروكلي', 'en' => 'with broccoli', 'items' => [['key' => 'broccoli', 'grams' => 150], ['key' => 'olive_oil', 'grams' => 5]]],
            ['ar' => 'مع كوسا مشوية', 'en' => 'with grilled zucchini', 'items' => [['key' => 'zucchini', 'grams' => 180], ['key' => 'garlic', 'grams' => 5], ['key' => 'olive_oil', 'grams' => 5]]],
            ['ar' => 'مع برغل', 'en' => 'with bulgur', 'items' => [['key' => 'bulgur_cooked', 'grams' => 120], ['key' => 'parsley', 'grams' => 15]]],
            ['ar' => 'مع بطاطس مسلوقة', 'en' => 'with boiled potato', 'items' => [['key' => 'potato_boiled', 'grams' => 150], ['key' => 'green_beans', 'grams' => 80]]],
            ['ar' => 'ستايل مندي خفيف', 'en' => 'light mandi style', 'items' => [['key' => 'rice_brown_cooked', 'grams' => 120], ['key' => 'onion', 'grams' => 40], ['key' => 'mixed_vegetables', 'grams' => 80], ['key' => 'olive_oil', 'grams' => 5]]],
            ['ar' => 'مع لبنة خفيفة', 'en' => 'with light labneh', 'items' => [['key' => 'labneh', 'grams' => 40], ['key' => 'cucumber', 'grams' => 100], ['key' => 'mint', 'grams' => 5]]],
            ['ar' => 'مع شوربة خضار', 'en' => 'with vegetable soup', 'items' => [['key' => 'mixed_vegetables', 'grams' => 150], ['key' => 'broth_chicken', 'grams' => 200]]],
            ['ar' => 'مع ذرة وبازلاء', 'en' => 'with corn and peas', 'items' => [['key' => 'corn', 'grams' => 80], ['key' => 'peas', 'grams' => 80], ['key' => 'carrot', 'grams' => 50]]],
        ];

        return $this->combine('dn', 'dinner', $bases, $addOns, 80);
    }

    /** @return list<array> */
    private function snackCombinations(): array
    {
        $bases = [
            ['ar' => 'تمر', 'en' => 'Dates snack', 'items' => [['key' => 'dates', 'grams' => 25]]],
            ['ar' => 'زبادي', 'en' => 'Yogurt snack', 'items' => [['key' => 'greek_yogurt_nonfat', 'grams' => 120]]],
            ['ar' => 'تفاح', 'en' => 'Apple snack', 'items' => [['key' => 'apple', 'grams' => 150]]],
            ['ar' => 'موز', 'en' => 'Banana snack', 'items' => [['key' => 'banana', 'grams' => 100]]],
            ['ar' => 'حمص', 'en' => 'Hummus snack', 'items' => [['key' => 'hummus', 'grams' => 50]]],
            ['ar' => 'لبنة', 'en' => 'Labneh snack', 'items' => [['key' => 'labneh', 'grams' => 50]]],
        ];
        $addOns = [
            ['ar' => 'ولوز', 'en' => 'and almonds', 'items' => [['key' => 'almonds', 'grams' => 12]]],
            ['ar' => 'وجوز', 'en' => 'and walnuts', 'items' => [['key' => 'walnuts', 'grams' => 10]]],
            ['ar' => 'وخيار', 'en' => 'and cucumber', 'items' => [['key' => 'cucumber', 'grams' => 100]]],
            ['ar' => 'وجزر', 'en' => 'and carrots', 'items' => [['key' => 'carrot', 'grams' => 80]]],
            ['ar' => 'وعسل خفيف', 'en' => 'and light honey', 'items' => [['key' => 'honey', 'grams' => 6]]],
            ['ar' => 'وزبيب', 'en' => 'and raisins', 'items' => [['key' => 'raisins', 'grams' => 15]]],
            ['ar' => 'وبرتقال', 'en' => 'and orange', 'items' => [['key' => 'orange', 'grams' => 130]]],
        ];

        return $this->combine('sn', 'snack', $bases, $addOns, 50);
    }

    /**
     * @param  list<array{ar: string, en: string, items: list}>  $bases
     * @param  list<array{ar: string, en: string, items: list}>  $addOns
     * @return list<array>
     */
    private function combine(string $prefix, string $mealType, array $bases, array $addOns, int $limit): array
    {
        $out = [];
        $n = 0;
        foreach ($bases as $bi => $base) {
            foreach ($addOns as $ai => $addon) {
                if ($n >= $limit) {
                    return $out;
                }
                $n++;
                $id = sprintf('%s-%02d-%02d', $prefix, $bi + 1, $ai + 1);
                $out[] = $this->r(
                    $id,
                    $mealType,
                    $base['ar'].' '.$addon['ar'],
                    $base['en'].' '.$addon['en'],
                    array_merge($base['items'], $addon['items']),
                    $mealType === 'snack' ? 5 : 15,
                    $mealType === 'snack' ? 0 : 20,
                    'easy'
                );
            }
        }

        return $out;
    }

    /** @return array<string, mixed> */
    private function padRecipe(int $n): array
    {
        $proteins = [
            ['ar' => 'دجاج', 'en' => 'Chicken', 'key' => 'chicken_breast_cooked', 'g' => 130],
            ['ar' => 'سمك', 'en' => 'Fish', 'key' => 'fish_white_cooked', 'g' => 140],
            ['ar' => 'عدس', 'en' => 'Lentils', 'key' => 'lentils_cooked', 'g' => 180],
        ];
        $sides = [
            ['ar' => 'سلطة', 'en' => 'salad', 'items' => [['key' => 'lettuce', 'grams' => 80], ['key' => 'tomato', 'grams' => 70], ['key' => 'olive_oil', 'grams' => 5]]],
            ['ar' => 'أرز بني', 'en' => 'brown rice', 'items' => [['key' => 'rice_brown_cooked', 'grams' => 120]]],
            ['ar' => 'خضار مشوية', 'en' => 'grilled veggies', 'items' => [['key' => 'zucchini', 'grams' => 100], ['key' => 'bell_pepper', 'grams' => 80]]],
        ];
        $p = $proteins[($n - 1) % count($proteins)];
        $s = $sides[($n - 1) % count($sides)];
        $type = ['lunch', 'dinner', 'breakfast', 'snack'][($n - 1) % 4];

        return $this->r(
            sprintf('pad-%03d', $n),
            $type,
            'وجبة صحية: '.$p['ar'].' مع '.$s['ar'],
            'Healthy meal: '.$p['en'].' with '.$s['en'],
            array_merge([['key' => $p['key'], 'grams' => $p['g']]], $s['items']),
            15,
            20,
            'easy'
        );
    }
}
