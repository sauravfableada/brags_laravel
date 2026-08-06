<?php

namespace App\Http\Controllers\API;

use OpenApi\Annotations as OA;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    use ApiResponseTrait;

    private function getSettingsByPrefix($prefix)
    {
        return Setting::where('key', 'like', $prefix . '%')->pluck('value', 'key');
    }

    private function updateSettingsByPrefix(Request $request, $prefix, $allowedKeys)
    {
        $data = $request->only($allowedKeys);

        foreach ($data as $key => $value) {
            if (is_string($value) || is_numeric($value) || is_null($value)) {
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value]
                );
            }
        }

        return $this->getSettingsByPrefix($prefix);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/settings/smtp",
     *     summary="Get SMTP settings",
     *     tags={"Admin Settings"},
     *     security={{"passport": {}}},
     *     @OA\Response(response=200, description="SMTP settings fetched successfully")
     * )
     */
    public function getSmtp()
    {
        return $this->successResponse($this->getSettingsByPrefix('smtp_'), 'SMTP settings fetched successfully.');
    }

    /**
     * @OA\Post(
     *     path="/api/admin/settings/smtp",
     *     summary="Update SMTP settings",
     *     tags={"Admin Settings"},
     *     security={{"passport": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="smtp_host", type="string"),
     *                 @OA\Property(property="smtp_port", type="string"),
     *                 @OA\Property(property="smtp_username", type="string"),
     *                 @OA\Property(property="smtp_password", type="string"),
     *                 @OA\Property(property="smtp_encryption", type="string"),
     *                 @OA\Property(property="smtp_from_address", type="string"),
     *                 @OA\Property(property="smtp_from_name", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="SMTP settings updated successfully")
     * )
     */
    public function updateSmtp(Request $request)
    {
        $allowed = ['smtp_host', 'smtp_port', 'smtp_username', 'smtp_password', 'smtp_encryption', 'smtp_from_address', 'smtp_from_name'];
        $updated = $this->updateSettingsByPrefix($request, 'smtp_', $allowed);
        return $this->successResponse($updated, 'SMTP settings updated successfully.');
    }

    /**
     * @OA\Get(
     *     path="/api/admin/settings/twilio",
     *     summary="Get Twilio settings",
     *     tags={"Admin Settings"},
     *     security={{"passport": {}}},
     *     @OA\Response(response=200, description="Twilio settings fetched successfully")
     * )
     */
    public function getTwilio()
    {
        return $this->successResponse($this->getSettingsByPrefix('twilio_'), 'Twilio settings fetched successfully.');
    }

    /**
     * @OA\Post(
     *     path="/api/admin/settings/twilio",
     *     summary="Update Twilio settings",
     *     tags={"Admin Settings"},
     *     security={{"passport": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="twilio_sid", type="string"),
     *                 @OA\Property(property="twilio_auth_token", type="string"),
     *                 @OA\Property(property="twilio_from_number", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Twilio settings updated successfully")
     * )
     */
    public function updateTwilio(Request $request)
    {
        $allowed = ['twilio_sid', 'twilio_auth_token', 'twilio_from_number'];
        $updated = $this->updateSettingsByPrefix($request, 'twilio_', $allowed);
        return $this->successResponse($updated, 'Twilio settings updated successfully.');
    }

    /**
     * @OA\Get(
     *     path="/api/admin/settings/payment",
     *     summary="Get Payment (Stripe) settings",
     *     tags={"Admin Settings"},
     *     security={{"passport": {}}},
     *     @OA\Response(response=200, description="Payment settings fetched successfully")
     * )
     */
    public function getPayment()
    {
        return $this->successResponse($this->getSettingsByPrefix('stripe_'), 'Payment settings fetched successfully.');
    }

    /**
     * @OA\Post(
     *     path="/api/admin/settings/payment",
     *     summary="Update Payment (Stripe) settings",
     *     tags={"Admin Settings"},
     *     security={{"passport": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="stripe_key", type="string"),
     *                 @OA\Property(property="stripe_secret", type="string"),
     *                 @OA\Property(property="stripe_webhook_secret", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Payment settings updated successfully")
     * )
     */
    public function updatePayment(Request $request)
    {
        $allowed = ['stripe_key', 'stripe_secret', 'stripe_webhook_secret'];
        $updated = $this->updateSettingsByPrefix($request, 'stripe_', $allowed);
        return $this->successResponse($updated, 'Payment settings updated successfully.');
    }
}
