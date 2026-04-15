# Public Marketing Site

This folder contains a static, Arabic marketing site for the project.

## Structure

- `index.html`: Single-page marketing website (RTL, Arabic)
- `assets/css/styles.css`: Styles with responsive design and dark-mode support
- `assets/js/main.js`: Small interactivity (mobile nav, FAQ accordion, smooth scroll)
- `assets/img/`: Placeholders for screenshots and logos

## Local preview

You can open `index.html` directly in your browser, or serve it with any static server:

```bash
# Python
python -m http.server 8080 -d publicsite

# Node
npx serve publicsite -p 8080
```

Then visit: http://localhost:8080

## Linking to app demos

The site includes links to existing public routes:
- Meal plans: `/meal-plans-public`
- Nutrition discounts: `/nutrition-discounts`
- FAQs: `/faqs`
- Testimonials: `/testimonials`
- Language switch (example): `/lang/ar`
- Sign up: `/register`

If routes change, update the anchors in `index.html`.

## Deployment

Options:
- Host as static assets on any web host (Nginx/Apache/S3/Cloudflare Pages/GitHub Pages).
- If serving through Laravel, you can map a subdomain (e.g. `promo.example.com`) directly to this folder via the web server configuration.

## Customization

- Replace text content (hero, features, pricing, FAQs) as needed.
- Add real screenshots to `assets/img/` and update `img` `src` attributes.
- Update brand colors in `:root` variables inside `styles.css`.

