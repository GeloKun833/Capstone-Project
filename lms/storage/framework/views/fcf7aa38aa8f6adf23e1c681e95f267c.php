
<?php
    $defaults = [
        'name' => 'Panorama Montessori School Inc Sta Rosa Campus',
        'phone' => '(049302) 9290',
        'email' => 'panoramamontessorischool1985@gmail.com',
        'facebook' => 'https://www.facebook.com/pms.starosacampus',
    ];

    $schoolContact = \Illuminate\Support\Facades\Cache::remember('school.contact.cards', 300, function () use ($defaults) {
        try {
            $settings = \App\Models\SchoolSetting::getSettings();
            $phone = $settings->phone;
            $email = $settings->email;
            $facebook = $settings->facebook_url;

            // Ignore old placeholder values from initial SchoolSetting seed
            if (!$phone || in_array($phone, ['+1234567890', '1234567890'], true)) {
                $phone = $defaults['phone'];
            }
            if (!$email || $email === 'info@panoramamontessori.edu') {
                $email = $defaults['email'];
            }
            if (!$facebook) {
                $facebook = $defaults['facebook'];
            }

            return [
                'name' => $settings->website_name ?: $defaults['name'],
                'phone' => $phone,
                'email' => $email,
                'facebook' => $facebook,
            ];
        } catch (\Throwable $e) {
            return $defaults;
        }
    });
?>

<div class="row">
    <div class="col-xl-4 col-sm-6 col-12">
        <a href="tel:<?php echo e(preg_replace('/\D+/', '', $schoolContact['phone'])); ?>" class="text-decoration-none">
            <div class="card flex-fill fb sm-box">
                <div class="social-likes">
                    <p>Contact</p>
                    <h6><?php echo e($schoolContact['phone']); ?></h6>
                    <small class="text-white-50 d-block mt-1" style="opacity:.85;font-size:.75rem;line-height:1.3;">
                        <?php echo e($schoolContact['name']); ?>

                    </small>
                </div>
                <div class="social-boxs">
                    <i class="fas fa-phone-alt text-primary fs-2"></i>
                </div>
            </div>
        </a>
    </div>
    <div class="col-xl-4 col-sm-6 col-12">
        <a href="<?php echo e($schoolContact['facebook']); ?>" target="_blank" rel="noopener noreferrer" class="text-decoration-none">
            <div class="card flex-fill twitter sm-box">
                <div class="social-likes">
                    <p>Facebook</p>
                    <h6>Sta Rosa Campus</h6>
                    <small class="text-white-50 d-block mt-1" style="opacity:.85;font-size:.75rem;">
                        @pms.starosacampus
                    </small>
                </div>
                <div class="social-boxs">
                    <i class="fab fa-facebook-f text-info fs-2"></i>
                </div>
            </div>
        </a>
    </div>
    <div class="col-xl-4 col-sm-6 col-12">
        <a href="mailto:<?php echo e($schoolContact['email']); ?>" class="text-decoration-none">
            <div class="card flex-fill insta sm-box">
                <div class="social-likes">
                    <p>Gmail</p>
                    <h6 style="font-size:.95rem;word-break:break-all;"><?php echo e($schoolContact['email']); ?></h6>
                    <small class="text-white-50 d-block mt-1" style="opacity:.85;font-size:.75rem;">
                        Email the school
                    </small>
                </div>
                <div class="social-boxs">
                    <i class="fas fa-envelope text-warning fs-2"></i>
                </div>
            </div>
        </a>
    </div>
</div>
<?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/partials/dashboard_contact_cards.blade.php ENDPATH**/ ?>