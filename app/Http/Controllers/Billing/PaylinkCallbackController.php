<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Billing\Invoice;
use App\Services\Billing\PaylinkActivationService;
use App\Services\Billing\PaylinkService;
use Illuminate\Http\Request;

class PaylinkCallbackController extends Controller
{
    public function __construct(
        private readonly PaylinkService $paylink,
        private readonly PaylinkActivationService $activation,
    ) {
    }

    public function __invoke(Request $request)
    {
        $transactionNo = $request->query('transactionNo')
            ?: $request->query('TransactionNo')
            ?: $request->query('transaction_no');
        $orderNumber = $request->query('orderNumber')
            ?: $request->query('OrderNumber')
            ?: $request->query('merchantOrderNumber');

        $invoice = null;
        $status = 'pending';
        $message = 'تمت العودة من صفحة الدفع. ننتظر تأكيد عملية السداد من Paylink.';

        if ($transactionNo) {
            try {
                $paylinkInvoice = $this->paylink->getInvoice((string) $transactionNo);
                $normalizedStatus = $this->paylink->normalizeInvoiceStatus($paylinkInvoice['orderStatus'] ?? null);

                if ($normalizedStatus === 'PAID') {
                    $activation = $this->activation->activatePaidInvoice((string) $transactionNo, $orderNumber, 'callback');
                    $status = 'paid';
                    $message = match ($activation['status'] ?? null) {
                        'queued' => 'تم التحقق من الدفع بنجاح، وبدأ الآن تجهيز نسختك تلقائيا.',
                        'already_provisioned' => 'تم الدفع وهذه النسخة مفعلة بالفعل.',
                        'duplicate' => 'تم تسجيل الدفعة مسبقا ونسختك قيد التجهيز أو مفعلة بالفعل.',
                        default => 'تم استلام عملية الدفع، وسيتم تفعيل النسخة بعد التحقق النهائي خلال لحظات.',
                    };
                } elseif ($normalizedStatus === 'CANCELED') {
                    $status = 'canceled';
                    $message = 'تم إلغاء عملية الدفع أو انتهت صلاحية الفاتورة.';
                }
            } catch (\Throwable $e) {
                $status = 'pending';
                $message = 'تمت العودة من Paylink لكن تعذر التحقق من حالة الفاتورة حاليا. يرجى المحاولة بعد قليل.';
            }
        }

        if ($transactionNo || $orderNumber) {
            $invoice = Invoice::query()
                ->where(function ($query) use ($transactionNo, $orderNumber) {
                    if ($transactionNo) {
                        $query->where('provider_invoice_id', $transactionNo);
                    }

                    if ($orderNumber) {
                        $method = $transactionNo ? 'orWhere' : 'where';
                        $query->{$method}('number', $orderNumber);
                    }
                })
                ->latest('id')
                ->first();
        }

        return view('billing.paylink-callback', compact('status', 'message', 'invoice'));
    }
}
