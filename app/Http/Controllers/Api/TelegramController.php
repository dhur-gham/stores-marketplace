<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TelegramController extends BaseController
{
    /**
     * Get the Telegram activation link for the authenticated customer.
     */
    public function getActivationLink(Request $request): JsonResponse
    {
        $customer = $request->user();

        if (! $customer) {
            return $this->error_response('Unauthenticated', 401);
        }

        $activation_link = $customer->getTelegramDeepLink();

        return $this->success_response([
            'activation_link' => $activation_link,
            'is_activated' => $customer->hasTelegramActivated(),
        ], 'Activation link retrieved successfully');
    }
}
