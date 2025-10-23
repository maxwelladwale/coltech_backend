#!/bin/bash
# ============================================================================
# COLTECH API Test Commands
# Test all endpoints to verify they work
# ============================================================================

API_URL="http://127.0.0.1:8000/api"

echo "🧪 Testing COLTECH API..."
echo ""

# ----------------------------------------------------------------------------
# 1. Health Check
# ----------------------------------------------------------------------------
echo "1️⃣  Testing Health Check..."
curl -s "$API_URL/health" | jq
echo ""

# ----------------------------------------------------------------------------
# 2. Get All Products
# ----------------------------------------------------------------------------
echo "2️⃣  Testing Get All Products..."
curl -s "$API_URL/products" | jq '. | length'
echo "products found"
echo ""

# ----------------------------------------------------------------------------
# 3. Get MDVR Products
# ----------------------------------------------------------------------------
echo "3️⃣  Testing Get MDVR Products..."
curl -s "$API_URL/products/mdvrs" | jq '.[0] | {name, price, channels}'
echo ""

# ----------------------------------------------------------------------------
# 4. Get Single Product
# ----------------------------------------------------------------------------
echo "4️⃣  Testing Get Single Product..."
curl -s "$API_URL/products/1" | jq '{name, price, category, in_stock}'
echo ""

# ----------------------------------------------------------------------------
# 5. Check Stock
# ----------------------------------------------------------------------------
echo "5️⃣  Testing Check Stock..."
curl -s "$API_URL/products/1/stock" | jq
echo ""

# ----------------------------------------------------------------------------
# 6. Get Packages
# ----------------------------------------------------------------------------
echo "6️⃣  Testing Get Packages..."
curl -s "$API_URL/packages" | jq '.[0] | {name, total_price, discounted_price}'
echo ""

# ----------------------------------------------------------------------------
# 7. Get Partner Garages
# ----------------------------------------------------------------------------
echo "7️⃣  Testing Get Partner Garages..."
curl -s "$API_URL/garages" | jq '.[0] | {name, location, rating}'
echo ""

# ----------------------------------------------------------------------------
# 8. Check License Status (should return not found)
# ----------------------------------------------------------------------------
echo "8️⃣  Testing Check License Status..."
curl -s "$API_URL/licenses/check/KAA123B" | jq
echo ""

# ----------------------------------------------------------------------------
# 9. Get Renewal Price
# ----------------------------------------------------------------------------
echo "9️⃣  Testing Get Renewal Price..."
curl -s "$API_URL/licenses/renewal-price?type=ai" | jq
echo ""

# ----------------------------------------------------------------------------
# 10. Create Order (POST request)
# ----------------------------------------------------------------------------
echo "🔟 Testing Create Order..."
curl -s -X POST "$API_URL/orders" \
  -H "Content-Type: application/json" \
  -d '{
    "cartItems": [
      {
        "productId": "1",
        "quantity": 1
      }
    ],
    "shippingAddress": {
      "fullName": "John Doe",
      "phone": "+254712345678",
      "email": "john@example.com",
      "address": "123 Main Street",
      "city": "Nairobi",
      "county": "Nairobi",
      "postalCode": "00100"
    },
    "installationDetails": {
      "method": "technician",
      "vehicleRegistration": "KXX123Y",
      "vehicleMake": "Toyota",
      "vehicleModel": "Hilux"
    },
    "paymentMethod": "mpesa"
  }' | jq '{order_number, total, status, payment_status}'
echo ""

# ----------------------------------------------------------------------------
# 11. Search Products
# ----------------------------------------------------------------------------
echo "1️⃣1️⃣  Testing Search Products..."
curl -s "$API_URL/products/search?q=AI" | jq '. | length'
echo "products found with 'AI'"
echo ""

# ----------------------------------------------------------------------------
# 12. Get Blog Posts
# ----------------------------------------------------------------------------
echo "1️⃣2️⃣  Testing Get Blog Posts..."
curl -s "$API_URL/blog" | jq '. | length'
echo "blog posts found"
echo ""

echo "✅ All API tests completed!"