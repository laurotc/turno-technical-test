<?php

namespace App\Http\Controllers\Api;

use App\Contracts\ShippingLabelPurchaser;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreShippingLabelRequest;
use App\Http\Resources\ShippingLabelResource;
use App\Models\ShippingLabel;
use App\Services\EasyPost\EasyPostShippingLabelException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ShippingLabelController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min(max((int) $request->integer('per_page', 15), 1), 100);

        $labels = ShippingLabel::query()
            ->forUser($request->user())
            ->latest()
            ->paginate($perPage);

        return ShippingLabelResource::collection($labels);
    }

    public function store(
        StoreShippingLabelRequest $request,
        ShippingLabelPurchaser $purchaser,
    ): JsonResource|JsonResponse {
        $validated = $request->validated();

        try {
            $purchasedLabel = $purchaser->purchase(
                $validated['from_address'],
                $validated['to_address'],
                $validated['parcel'],
            );
        } catch (EasyPostShippingLabelException $exception) {
            return response()->json([
                'message' => 'Unable to create shipping label.',
                'error' => $exception->getMessage(),
            ], 422);
        }

        $label = ShippingLabel::create([
            'user_id' => $request->user()->id,
            ...$purchasedLabel->toDatabaseAttributes(),
        ]);

        return ShippingLabelResource::make($label)
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, ShippingLabel $shippingLabel): ShippingLabelResource
    {
        abort_unless($shippingLabel->user_id === $request->user()->id, 404);

        return ShippingLabelResource::make($shippingLabel);
    }

    public function print(Request $request, ShippingLabel $shippingLabel): RedirectResponse
    {
        abort_unless($shippingLabel->user_id === $request->user()->id, 404);

        $labelUrl = $shippingLabel->label_pdf_url ?: $shippingLabel->label_url;

        abort_unless(is_string($labelUrl) && $labelUrl !== '', 404);

        return redirect()->away($labelUrl);
    }
}
