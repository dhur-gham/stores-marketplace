# Production Update Steps

## 🚀 Quick Update Commands

Run these commands on your production server:

```bash
# 1. Navigate to project directory
cd /var/www/miniapp.dhurgham.dev

# 2. Pull latest code
git pull origin main

# 3. Install dependencies (if needed)
composer install --no-dev --optimize-autoloader

# 4. Run migrations (if any new ones)
php artisan migrate --force

# 5. Clear and cache config
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Optimize
php artisan optimize

# 7. Rebuild frontend (if needed)
npm ci
npm run build
```

## ✅ What Changed

- ✅ Improved PCI DSS error detection
- ✅ Better error messages for users
- ✅ Detailed error logging for debugging
- ✅ More informative error responses

## 🧪 Testing After Update

1. **Test Payment Flow:**
   - Create an order
   - Select "Online Payment"
   - Enter test card details
   - Submit payment

2. **Expected Result:**
   - You'll still get the PCI DSS error (this is expected)
   - But now you'll see: "Payment gateway configuration error. Your PayTabs account needs SAQ A-EP compliance enabled. Please contact PayTabs support."
   - Full error details will be logged in `storage/logs/laravel.log`

3. **Check Logs:**
   ```bash
   tail -f storage/logs/laravel.log | grep -i "pci\|paytabs"
   ```

## ⚠️ Important Note

**The PCI DSS error will still occur** until PayTabs enables SAQ A-EP on your account. The code update only improves error handling and messages.

## 📞 Next Step

**Contact PayTabs Support** with:
- Profile ID: 172391
- Error: "PCI DSS certification to a minimum of SAQ A-EP is required"
- Request: Enable SAQ A-EP compliance for managed form integration

---

**After PayTabs enables SAQ A-EP, payments will work immediately!** 🎉

