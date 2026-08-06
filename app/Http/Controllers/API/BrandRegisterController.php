<?php

namespace App\Http\Controllers\API;

use OpenApi\Annotations as OA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Requests\BrandRegisterRequest;
use App\Models\Brand;
use App\Models\BrandRegistrationProduct;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class BrandRegisterController extends Controller
{
    use ApiResponseTrait;

    /**
     * @OA\Post(
     *     path="/api/v1/brand/register",
     *     summary="Register a new brand",
     *     tags={"Brand"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="brand_name", type="string", description="Name of the brand"),
     *                 @OA\Property(property="brand_logo", type="string", format="binary", description="Brand logo image file"),
     *                 @OA\Property(property="trademark_office", type="string", description="Trademark office"),
     *                 @OA\Property(property="trademark_registration_number", type="string", description="Trademark registration number"),
     *                 @OA\Property(property="brand_description", type="string", description="Description of the brand"),
     *                 @OA\Property(property="business_name", type="string", description="Name of the business"),
     *                 @OA\Property(property="business_address", type="string", description="Full address of the business"),
     *                 @OA\Property(property="business_contact_email", type="string", description="Contact email"),
     *                 @OA\Property(property="primary_contact_name", type="string", description="Primary contact name"),
     *                 @OA\Property(property="phone_number_country", type="string", description="Phone number country code"),
     *                 @OA\Property(property="phone_number", type="string", description="Phone number"),
     *                 @OA\Property(property="website_url", type="string", description="Website URL"),
     *                 @OA\Property(property="password", type="string", description="Password"),
     *                 @OA\Property(property="password_confirmation", type="string", description="Confirm Password"),
     *                 @OA\Property(property="manufacturing_locations", type="string", description="Manufacturing locations"),
     *                 @OA\Property(property="distribution_channels", type="string", description="Distribution channels"),
     *                 @OA\Property(property="authorized_resellers", type="string", description="Authorized resellers"),
     *                 @OA\Property(property="product_supply_chain", type="string", description="Product supply chain"),
     *                 @OA\Property(property="product_categories", type="string", description="Product categories"),
     *                 @OA\Property(property="products[0][identifier]", type="string", description="Product 1 Identifier"),
     *                 @OA\Property(property="products[0][image]", type="string", format="binary", description="Product 1 Image"),
     *                 @OA\Property(property="sell_under_own_brand", type="boolean", description="Sell under own brand"),
     *                 @OA\Property(property="seller_email", type="string", description="Seller Email"),
     *                 @OA\Property(property="store_url", type="string", description="Store URL"),
     *                 @OA\Property(property="approve_resellers", type="boolean", description="Approve resellers"),
     *                 @OA\Property(property="additional_documentation", type="string", format="binary", description="Additional Documentation")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Brand registered successfully"),
     *     @OA\Response(response=422, description="Validation Error"),
     *     @OA\Response(response=500, description="Server Error")
     * )
     */
    public function register(BrandRegisterRequest $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validated();

            // Create User
            $user = User::create([
                'name' => $validated['primary_contact_name'],
                'email' => $validated['business_contact_email'],
                'password' => Hash::make($validated['password']),
            ]);
            
            // Assign role if you use Spatie permissions, e.g., $user->assignRole('brand_seller');

            // Handle file uploads for brand
            $brandLogoPath = $request->file('brand_logo')->store('brands/logos', 'public');
            
            $additionalDocPath = null;
            if ($request->hasFile('additional_documentation')) {
                $additionalDocPath = $request->file('additional_documentation')->store('brands/docs', 'public');
            }

            // Create Brand
            $brand = Brand::create([
                'user_id' => $user->id,
                'brand_name' => $validated['brand_name'],
                'brand_logo' => $brandLogoPath,
                'trademark_office' => $validated['trademark_office'],
                'trademark_registration_number' => $validated['trademark_registration_number'],
                'brand_description' => $validated['brand_description'],
                
                'business_name' => $validated['business_name'],
                'business_address' => $validated['business_address'],
                'business_contact_email' => $validated['business_contact_email'],
                'primary_contact_name' => $validated['primary_contact_name'],
                'phone_number_country' => $validated['phone_number_country'] ?? null,
                'phone_number' => $validated['phone_number'] ?? null,
                'website_url' => $validated['website_url'] ?? null,
                
                'manufacturing_locations' => $validated['manufacturing_locations'],
                'distribution_channels' => $validated['distribution_channels'],
                'authorized_resellers' => $validated['authorized_resellers'] ?? null,
                'product_supply_chain' => $validated['product_supply_chain'] ?? null,
                
                'product_categories' => $validated['product_categories'],
                
                'sell_under_own_brand' => $validated['sell_under_own_brand'],
                'seller_email' => $validated['seller_email'] ?? null,
                'store_url' => $validated['store_url'] ?? null,
                'approve_resellers' => $validated['approve_resellers'],
                'additional_documentation' => $additionalDocPath,
            ]);

            // Handle products
            foreach ($validated['products'] as $index => $productData) {
                // The uploaded file will be available in $request->file("products.{$index}.image")
                $productImagePath = $request->file("products.{$index}.image")->store('brands/products', 'public');
                
                BrandRegistrationProduct::create([
                    'brand_id' => $brand->id,
                    'product_identifier' => $productData['identifier'],
                    'product_image' => $productImagePath,
                ]);
            }

            DB::commit();

            return $this->successResponse(
                ['user' => $user, 'brand' => $brand->load('registrationProducts')],
                'Brand registered successfully.',
                201
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Registration failed: ' . $e->getMessage(), 500);
        }
    }
}
