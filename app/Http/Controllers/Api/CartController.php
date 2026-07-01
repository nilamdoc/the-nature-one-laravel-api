<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class CartController extends Controller
{
    /**
     * 🔹 GET CART
     */
    public function index()
    {
        try {
            $cart = session()->get('cart', []);

            return response()->json([
                'status' => true,
                'data' => $this->formatCart($cart)
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch cart',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🔹 ADD TO CART
     */
    public function add(Request $request)
    {
        try {
            $data = $request->validate([
                'product_id' => 'required|integer',
                'name' => 'required|string',
                'price' => 'required|numeric',
                'image' => 'nullable|string',
                'quantity' => 'nullable|integer|min:1'
            ]);

            $cart = session()->get('cart', []);

            $found = false;

            foreach ($cart as &$item) {
                if ($item['product_id'] == $data['product_id']) {
                    $item['quantity'] += $data['quantity'] ?? 1;
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $cart[] = [
                    'product_id' => $data['product_id'],
                    'name' => $data['name'],
                    'price' => $data['price'],
                    'image' => $data['image'] ?? null,
                    'quantity' => $data['quantity'] ?? 1,
                ];
            }

            session()->put('cart', $cart);

            return response()->json([
                'status' => true,
                'message' => 'Added to cart',
                'data' => $this->formatCart($cart)
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Add failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🔹 UPDATE QUANTITY
     */
    public function update(Request $request)
    {
        try {
            $data = $request->validate([
                'product_id' => 'required|integer',
                'quantity' => 'required|integer|min:1'
            ]);

            $cart = session()->get('cart', []);

            foreach ($cart as &$item) {
                if ($item['product_id'] == $data['product_id']) {
                    $item['quantity'] = $data['quantity'];
                    break;
                }
            }

            session()->put('cart', $cart);

            return response()->json([
                'status' => true,
                'message' => 'Cart updated',
                'data' => $this->formatCart($cart)
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🔹 REMOVE ITEM
     */
    public function remove(Request $request)
    {
        try {
            $data = $request->validate([
                'product_id' => 'required|integer'
            ]);

            $cart = session()->get('cart', []);

            $cart = array_values(array_filter($cart, function ($item) use ($data) {
                return $item['product_id'] != $data['product_id'];
            }));

            session()->put('cart', $cart);

            return response()->json([
                'status' => true,
                'message' => 'Item removed',
                'data' => $this->formatCart($cart)
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Remove failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🔹 CLEAR CART
     */
    public function clear()
    {
        session()->forget('cart');

        return response()->json([
            'status' => true,
            'message' => 'Cart cleared'
        ]);
    }

    /**
     * 🔹 FORMAT CART (Totals Calculation)
     */
    private function formatCart($cart)
    {
        $total = 0;
        $totalQty = 0;

        foreach ($cart as &$item) {
            $item['subtotal'] = $item['price'] * $item['quantity'];
            $total += $item['subtotal'];
            $totalQty += $item['quantity'];
        }

        return [
            'items' => $cart,
            'total_amount' => $total,
            'total_quantity' => $totalQty
        ];
    }
}