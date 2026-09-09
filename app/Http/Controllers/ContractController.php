<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use App\Support\Contracts;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Http\Request;

/**
 * صفحة العقد لدى العميل: مراجعة البنود بنداً بنداً، تسجيل القرارات، وطباعة العقد.
 * تعمل عبر الرابط المخصّص (/quote/{invite}/contract) أو رابط موقّع للنموذج العام،
 * بالنمط نفسه المتّبع في عرض السعر.
 */
class ContractController extends Controller
{
    /** صفحة مراجعة العقد عبر الرابط المخصّص. */
    public function review(string $invite)
    {
        [$sr, $client] = $this->resolve($invite);

        return $this->reviewView($sr, $client);
    }

    /** الصفحة نفسها عبر رابط موقّع للنموذج العام. */
    public function reviewSigned(ServiceRequest $serviceRequest)
    {
        return $this->reviewView($serviceRequest, null);
    }

    /** تسجيل قرارات العميل عبر الرابط المخصّص. */
    public function decision(Request $request, string $invite)
    {
        [$sr] = $this->resolve($invite);

        return $this->storeDecisions($request, $sr);
    }

    /** تسجيل القرارات عبر رابط موقّع للنموذج العام. */
    public function decisionSigned(Request $request, ServiceRequest $serviceRequest)
    {
        return $this->storeDecisions($request, $serviceRequest);
    }

    /** @return array{0: ServiceRequest, 1: array} */
    private function resolve(string $invite): array
    {
        $client = QuoteController::INVITES[$invite] ?? abort(404);

        $sr = ServiceRequest::query()
            ->where('source', 'quote_link:'.$invite)
            ->latest('id')
            ->first() ?? abort(404);

        return [$sr, $client];
    }

    private function reviewView(ServiceRequest $sr, ?array $client)
    {
        $contract = Contracts::of($sr) ?? abort(404);

        // المسوّدة الداخلية لا تُعرض للعميل قبل إرسالها إليه
        abort_unless($contract['is_sent'], 404);

        $quote = QuoteController::quoteOf($sr);

        return view('contracts.review', [
            'sr' => $sr,
            'client' => $client,
            'contract' => $contract,
            'quote' => $quote,
            'contact' => [
                'name' => $client['name'] ?? $sr->name,
                'phone' => $this->pick($sr->phone, $client['phone'] ?? null),
                'email' => $this->pick($sr->email, $client['email'] ?? null),
                'store' => $this->pick($sr->company, $client['store_name'] ?? null),
            ],
            'bank' => QuoteController::bankDetails(),
            'signature' => Contracts::signature(),
            'qr' => $this->qrSvg($contract['number']),
            'decisionUrl' => Contracts::decisionUrl($sr),
            'proposalUrl' => QuoteController::proposalUrl($sr),
        ]);
    }

    private function pick(?string $stored, ?string $fallback): ?string
    {
        $stored = trim((string) $stored);

        return $stored !== '' && $stored !== '—' ? $stored : ($fallback ?: null);
    }

    private function storeDecisions(Request $request, ServiceRequest $sr)
    {
        $contract = Contracts::of($sr) ?? abort(404);
        abort_unless($contract['is_sent'], 404);

        if ($blocker = Contracts::decisionBlocker($contract['status'])) {
            return back()->withErrors(['contract' => $blocker]);
        }

        $data = $request->validate([
            'clauses' => ['present', 'array'],
            'clauses.*.decision' => ['nullable', 'string', 'in:'.implode(',', array_keys(Contracts::DECISIONS))],
            'clauses.*.note' => ['nullable', 'string', 'max:'.Contracts::NOTE_MAX],
        ], [], ['clauses' => 'القرارات', 'clauses.*.decision' => 'القرار', 'clauses.*.note' => 'الملاحظة']);

        $result = Contracts::applyDecisions($sr, array_map(fn ($c) => (array) $c, $data['clauses']));

        if (! $result['ok']) {
            return back()->withInput()->withErrors(['contract' => $result['error']]);
        }

        return back()->with('contract_saved', $result['outcome']);
    }

    /** رمز QR لرقم العقد — SVG مضمّن دون أي اعتماد خارجي. */
    private function qrSvg(string $number): string
    {
        $options = new QROptions([
            'outputType' => QROutputInterface::MARKUP_SVG,
            'eccLevel' => EccLevel::M,
            'version' => 3,
            'addQuietzone' => true,
            'quietzoneSize' => 1,
            'outputBase64' => false,
            'svgUseFillAttributes' => false,
            'drawLightModules' => false,
            'connectPaths' => true,
            'cssClass' => 'qr',
        ]);

        return preg_replace('/<\?xml.*?\?>\s*/s', '', (new QRCode($options))->render($number));
    }
}
