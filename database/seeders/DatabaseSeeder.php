<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedRolesAndAdmin();
        $this->seedSettings();
        $this->seedServices();
        $this->seedPages();
        $this->seedDemoStore();
    }

    private function seedRolesAndAdmin(): void
    {
        foreach (['super_admin', 'admin', 'staff'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        $admin = User::updateOrCreate(
            ['email' => 'admin@wareed.vip'],
            ['name' => 'مدير وريد', 'password' => Hash::make('Wareed@2026')]
        );
        $admin->syncRoles(['super_admin']);
    }

    private function seedSettings(): void
    {
        $settings = [
            ['site_name', ['ar' => 'وريد', 'en' => 'Wareed'], 'general'],
            ['site_tagline', ['ar' => 'منصتك التقنية المتكاملة', 'en' => 'Your all-in-one tech platform'], 'general'],
            ['site_description', ['ar' => 'منصة وريد: متاجر إلكترونية من ضغطة زر، حلول تقنية للشركات والجهات، وتدريب تقني احترافي — كل ذلك في منصة واحدة.', 'en' => 'Wareed: one-click online stores, tech solutions for companies and organizations, and professional tech training — all in one platform.'], 'general'],
            ['hero_title', ['ar' => 'منصتك التقنية', 'en' => 'Your all-in-one'], 'general'],
            ['hero_title_accent', ['ar' => 'المتكاملة', 'en' => 'tech platform'], 'general'],
            ['hero_subtitle', ['ar' => 'متجرك الإلكتروني من ضغطة زر، حلول تقنية للشركات والجهات، وتدريب احترافي في البرمجة والذكاء الاصطناعي — كل ذلك في منصة واحدة.', 'en' => 'Your online store with one click, tech solutions for companies and organizations, and professional training in programming and AI — all in one platform.'], 'general'],
            ['contact_phone', '+20 100 123 4567', 'contact'],
            ['contact_email', 'info@wareed.vip', 'contact'],
            ['contact_whatsapp', '201001234567', 'contact'],
            ['contact_address', ['ar' => 'نعمل عن بُعد حول العالم', 'en' => 'We work remotely, worldwide'], 'contact'],
            ['social_facebook', 'https://facebook.com', 'social'],
            ['social_instagram', 'https://instagram.com', 'social'],
            ['social_linkedin', 'https://linkedin.com', 'social'],
            ['social_tiktok', 'https://tiktok.com', 'social'],
            ['seo_keywords', ['ar' => 'متجر إلكتروني, إنشاء متجر, حلول تقنية, تدريب برمجة, ذكاء اصطناعي, وريد', 'en' => 'online store, ecommerce, tech solutions, programming training, AI, Wareed'], 'seo'],
        ];

        foreach ($settings as [$key, $value, $group]) {
            Setting::set($key, $value, $group);
        }
    }

    private function feat(string $icon, string $tAr, string $tEn, string $dAr, string $dEn): array
    {
        return ['icon' => $icon, 'title' => ['ar' => $tAr, 'en' => $tEn], 'desc' => ['ar' => $dAr, 'en' => $dEn]];
    }

    private function faq(string $qAr, string $qEn, string $aAr, string $aEn): array
    {
        return ['q' => ['ar' => $qAr, 'en' => $qEn], 'a' => ['ar' => $aAr, 'en' => $aEn]];
    }

    private function seedServices(): void
    {
        Service::updateOrCreate(['key' => 'ecommerce'], [
            'slug' => 'ecommerce',
            'name' => ['ar' => 'المتاجر الإلكترونية', 'en' => 'E-commerce Stores'],
            'tagline' => ['ar' => 'متجرك من ضغطة زر', 'en' => 'Your store, one click away'],
            'icon' => '🛒',
            'color' => '#3B82F6',
            'summary' => ['ar' => 'أنشئ متجرك الإلكتروني الاحترافي في دقائق، بطرق دفع وشحن متعددة، وتسعير شفاف بدون عمولة خفية.', 'en' => 'Launch your professional online store in minutes, with multiple payment and shipping options and transparent pricing — no hidden fees.'],
            'hero_title' => ['ar' => 'متجرك الإلكتروني الاحترافي من ضغطة زر', 'en' => 'Your professional online store, one click away'],
            'hero_subtitle' => ['ar' => 'كل ما تحتاجه لبيع منتجاتك أونلاين: تصميم فاخر، طرق دفع متعددة (محافظ إلكترونية وبطاقات والدفع عند الاستلام)، شحن، وSEO تلقائي يصدّرك في جوجل.', 'en' => 'Everything to sell online: a luxurious design, multiple payment options (e-wallets, cards and Cash on Delivery), shipping, and automatic SEO that ranks you on Google.'],
            'cta_label' => ['ar' => 'أنشئ متجرك مجاناً', 'en' => 'Create your store — free'],
            'features' => [
                $this->feat('⚡', 'تجهيز فوري', 'Instant setup', 'متجر جاهز خلال دقائق بقوالب فاخرة متجاوبة مع الموبايل.', 'A store ready in minutes with luxurious, mobile-responsive templates.'),
                $this->feat('💳', 'طرق دفع متعددة', 'Multiple payments', 'محافظ إلكترونية، بطاقات، والدفع عند الاستلام عبر بوابات موثوقة.', 'E-wallets, cards, and Cash on Delivery via trusted gateways.'),
                $this->feat('🚚', 'شحن متكامل', 'Integrated shipping', 'ربط مع شركات الشحن وحساب تلقائي للأسعار.', 'Integration with couriers and automatic rate calculation.'),
                $this->feat('🔍', 'SEO تلقائي', 'Automatic SEO', 'كل منتج وصفحة مهيّأة للظهور أول نتائج جوجل تلقائياً.', 'Every product and page is auto-optimized to rank on Google.'),
                $this->feat('📊', 'لوحة تحكم', 'Dashboard', 'إدارة المنتجات والطلبات والعملاء من مكان واحد بسهولة.', 'Manage products, orders and customers easily from one place.'),
                $this->feat('🛡️', 'أمان وموثوقية', 'Security & reliability', 'حماية بيانات، نسخ احتياطي، ودعم بشري على مدار الساعة.', 'Data protection, backups, and 24/7 human support.'),
            ],
            'pricing' => [
                ['name' => ['ar' => 'البداية', 'en' => 'Starter'], 'price' => ['ar' => 'مجاني', 'en' => 'Free'], 'period' => ['ar' => 'للأبد', 'en' => 'forever'], 'features' => ['ar' => ['متجر كامل', 'حتى 20 منتج', 'الدفع عند الاستلام', 'نطاق فرعي wareed'], 'en' => ['Full store', 'Up to 20 products', 'Cash on Delivery', 'wareed subdomain']], 'featured' => false],
                ['name' => ['ar' => 'النمو', 'en' => 'Growth'], 'price' => ['ar' => '$29', 'en' => '$29'], 'period' => ['ar' => '/ شهرياً', 'en' => '/ month'], 'features' => ['ar' => ['منتجات غير محدودة', 'كل طرق الدفع', 'نطاقك الخاص', 'تقارير متقدمة', 'دعم أولوية'], 'en' => ['Unlimited products', 'All payment methods', 'Your own domain', 'Advanced reports', 'Priority support']], 'featured' => true],
                ['name' => ['ar' => 'الاحترافي', 'en' => 'Pro'], 'price' => ['ar' => '$99', 'en' => '$99'], 'period' => ['ar' => '/ شهرياً', 'en' => '/ month'], 'features' => ['ar' => ['كل مزايا النمو', 'متعدد الفروع', 'تكامل API', 'مدير حساب مخصص'], 'en' => ['All Growth features', 'Multi-branch', 'API integration', 'Dedicated account manager']], 'featured' => false],
            ],
            'faqs' => [
                $this->faq('هل أحتاج خبرة تقنية؟', 'Do I need technical experience?', 'لا إطلاقاً، المنصة سهلة وتُنشئ متجرك بضغطة زر دون أي برمجة.', 'Not at all — the platform is easy and builds your store with one click, no coding.'),
                $this->faq('ما طرق الدفع المدعومة؟', 'Which payment methods are supported?', 'محافظ إلكترونية، بطاقات، والدفع عند الاستلام عبر بوابات موثوقة.', 'E-wallets, cards, and Cash on Delivery via trusted gateways.'),
                $this->faq('هل التسعير شفاف؟', 'Is pricing transparent?', 'نعم، كل الأسعار واضحة وبدون أي عمولة خفية على مبيعاتك.', 'Yes, all prices are clear with no hidden commission on your sales.'),
            ],
            'form_fields' => [
                ['name' => 'business_type', 'label' => 'نوع النشاط', 'type' => 'select', 'options' => ['ملابس وأزياء', 'إلكترونيات', 'أطعمة ومشروبات', 'مستحضرات تجميل', 'أخرى']],
            ],
            'meta_title' => ['ar' => 'إنشاء متجر إلكتروني احترافي من ضغطة زر', 'en' => 'Create a professional online store with one click'],
            'meta_description' => ['ar' => 'أنشئ متجرك الإلكتروني الاحترافي في دقائق مع وريد: طرق دفع متعددة، شحن، وSEO تلقائي. ابدأ مجاناً.', 'en' => 'Launch your professional online store in minutes with Wareed: multiple payments, shipping, and automatic SEO. Start free.'],
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Service::updateOrCreate(['key' => 'tech_solution'], [
            'slug' => 'solutions',
            'name' => ['ar' => 'الحلول التقنية', 'en' => 'Tech Solutions'],
            'tagline' => ['ar' => 'حلول للشركات والجهات', 'en' => 'Solutions for companies & organizations'],
            'icon' => '🧩',
            'color' => '#8B5CF6',
            'summary' => ['ar' => 'نصمّم وننفّذ حلولاً تقنية (Hardware/Software) مخصّصة للجهات والشركات لحل مشكلاتها وأتمتة أعمالها.', 'en' => 'We design and build custom tech solutions (hardware/software) for organizations and companies to solve problems and automate operations.'],
            'hero_title' => ['ar' => 'حلول تقنية مؤسسية تُحدث الفرق', 'en' => 'Enterprise tech solutions that make a difference'],
            'hero_subtitle' => ['ar' => 'من التحول الرقمي والأنظمة المخصّصة إلى البنية التحتية والأمن السيبراني — نبني الحل التقني الذي يحل مشكلتك بدقة وموثوقية.', 'en' => 'From digital transformation and custom systems to infrastructure and cybersecurity — we build the solution that solves your problem reliably.'],
            'cta_label' => ['ar' => 'اطلب عرض سعر', 'en' => 'Request a quote'],
            'features' => [
                $this->feat('🔎', 'تحليل واستشارة', 'Analysis & consulting', 'دراسة احتياجك وتصميم الحل الأنسب لأهدافك وميزانيتك.', 'Studying your needs and designing the best solution for your goals and budget.'),
                $this->feat('⚙️', 'تطوير مخصّص', 'Custom development', 'أنظمة وتطبيقات ويب وموبايل مبنية خصيصاً لك.', 'Web and mobile systems and apps built specifically for you.'),
                $this->feat('🏛️', 'حلول للجهات', 'Enterprise solutions', 'خبرة في مشاريع التحول الرقمي للجهات والمناقصات.', 'Experience in digital transformation projects and tenders.'),
                $this->feat('🔐', 'أمن سيبراني', 'Cybersecurity', 'حماية الأنظمة والبيانات وفق أفضل المعايير.', 'Protecting systems and data to the highest standards.'),
                $this->feat('☁️', 'بنية تحتية', 'Infrastructure', 'خوادم، سحابة، وشبكات موثوقة وقابلة للتوسّع.', 'Reliable, scalable servers, cloud and networks.'),
                $this->feat('🤝', 'دعم وصيانة', 'Support & maintenance', 'اتفاقيات مستوى خدمة (SLA) ودعم مستمر.', 'Service-level agreements (SLA) and ongoing support.'),
            ],
            'faqs' => [
                $this->faq('هل تعملون مع الجهات الكبيرة؟', 'Do you work with large organizations?', 'نعم، لدينا خبرة في مشاريع التحول الرقمي والمناقصات للجهات والشركات.', 'Yes, we have experience in digital transformation projects and tenders for organizations and companies.'),
                $this->faq('كيف يتم التسعير؟', 'How is pricing determined?', 'حسب نطاق المشروع. تواصل معنا لعرض سعر مفصّل ومجاني.', 'Based on project scope. Contact us for a detailed, free quote.'),
            ],
            'form_fields' => [
                ['name' => 'entity_type', 'label' => 'نوع الجهة', 'type' => 'select', 'options' => ['جهة حكومية', 'شركة خاصة', 'مؤسسة', 'أخرى']],
                ['name' => 'solution_type', 'label' => 'نوع الحل المطلوب', 'type' => 'select', 'options' => ['نظام إداري', 'تطبيق موبايل', 'موقع/منصة', 'أمن سيبراني', 'بنية تحتية', 'أخرى']],
            ],
            'meta_title' => ['ar' => 'حلول تقنية للشركات والجهات', 'en' => 'Tech solutions for companies and organizations'],
            'meta_description' => ['ar' => 'وريد تقدّم حلولاً تقنية مخصّصة (سوفت وير وهارد وير) للجهات والشركات: تطوير أنظمة، تحول رقمي، وأمن سيبراني.', 'en' => 'Wareed delivers custom tech solutions (software & hardware) for organizations and companies: systems, digital transformation, and cybersecurity.'],
            'sort_order' => 2,
            'is_active' => true,
        ]);

        Service::updateOrCreate(['key' => 'training'], [
            'slug' => 'training',
            'name' => ['ar' => 'التدريب التقني', 'en' => 'Tech Training'],
            'tagline' => ['ar' => 'تدريب الشركات وتدريب المدربين', 'en' => 'Corporate training & training of trainers'],
            'icon' => '🎓',
            'color' => '#2DD4BF',
            'summary' => ['ar' => 'برامج تدريب تقني احترافية للجهات والشركات وتدريب المدربين في الذكاء الاصطناعي والبرمجة وقواعد البيانات والخوادم.', 'en' => 'Professional tech training programs for organizations and companies, plus training of trainers in AI, programming, databases and servers.'],
            'hero_title' => ['ar' => 'تدريب تقني يصنع الخبراء', 'en' => 'Tech training that builds experts'],
            'hero_subtitle' => ['ar' => 'مسارات احترافية في الذكاء الاصطناعي، Backend، Frontend، قواعد البيانات، والخوادم — للشركات والجهات، مع برامج متخصّصة لتدريب المدربين (ToT).', 'en' => 'Professional tracks in AI, Backend, Frontend, Databases and Servers — for companies and organizations, plus specialized Training of Trainers (ToT) programs.'],
            'cta_label' => ['ar' => 'اطلب برنامجاً تدريبياً', 'en' => 'Request a training program'],
            'features' => [
                $this->feat('🤖', 'الذكاء الاصطناعي', 'Artificial Intelligence', 'تعلّم الآلة، التعلّم العميق، وتطبيقات الـ AI العملية.', 'Machine learning, deep learning, and practical AI applications.'),
                $this->feat('🧱', 'Backend', 'Backend', 'PHP/Laravel، Node.js، APIs، وبناء أنظمة قوية.', 'PHP/Laravel, Node.js, APIs, and building robust systems.'),
                $this->feat('🎨', 'Frontend', 'Frontend', 'HTML/CSS/JS، React، وتصميم واجهات حديثة.', 'HTML/CSS/JS, React, and modern UI design.'),
                $this->feat('🗄️', 'قواعد البيانات', 'Databases', 'MySQL، PostgreSQL، النمذجة والتحسين.', 'MySQL, PostgreSQL, modeling and optimization.'),
                $this->feat('🖥️', 'الخوادم والـ DevOps', 'Servers & DevOps', 'Linux، النشر، الحاويات، والبنية السحابية.', 'Linux, deployment, containers, and cloud infrastructure.'),
                $this->feat('👨‍🏫', 'تدريب المدربين (ToT)', 'Training of Trainers (ToT)', 'إعداد مدربين تقنيين معتمدين للجهات والأكاديميات.', 'Preparing certified technical trainers for organizations and academies.'),
            ],
            'pricing' => [
                ['name' => ['ar' => 'للأفراد', 'en' => 'Individuals'], 'price' => ['ar' => 'حسب المسار', 'en' => 'Per track'], 'period' => ['ar' => '', 'en' => ''], 'features' => ['ar' => ['مسارات متخصّصة', 'مشاريع عملية', 'شهادة إتمام'], 'en' => ['Specialized tracks', 'Hands-on projects', 'Completion certificate']], 'featured' => false],
                ['name' => ['ar' => 'للشركات', 'en' => 'Companies'], 'price' => ['ar' => 'عرض مخصّص', 'en' => 'Custom quote'], 'period' => ['ar' => '', 'en' => ''], 'features' => ['ar' => ['تدريب فرق العمل', 'محتوى مخصّص', 'تقييم وقياس أثر'], 'en' => ['Team training', 'Custom content', 'Assessment & impact measurement']], 'featured' => true],
                ['name' => ['ar' => 'تدريب المدربين', 'en' => 'Training of Trainers'], 'price' => ['ar' => 'عرض مخصّص', 'en' => 'Custom quote'], 'period' => ['ar' => '', 'en' => ''], 'features' => ['ar' => ['برامج ToT', 'اعتماد جودة', 'متابعة'], 'en' => ['ToT programs', 'Quality accreditation', 'Follow-up']], 'featured' => false],
            ],
            'faqs' => [
                $this->faq('هل التدريب أونلاين أم حضوري؟', 'Is training online or in-person?', 'نوفّر الخيارين حسب احتياج الجهة: حضوري، أونلاين، أو مدمج.', 'We offer both depending on your needs: in-person, online, or blended.'),
                $this->faq('هل تقدّمون شهادات؟', 'Do you provide certificates?', 'نعم، شهادات إتمام معتمدة، مع برامج اعتماد لتدريب المدربين.', 'Yes, accredited completion certificates, plus accreditation programs for training of trainers.'),
            ],
            'form_fields' => [
                ['name' => 'track', 'label' => 'المسار التدريبي', 'type' => 'select', 'options' => ['الذكاء الاصطناعي', 'Backend', 'Frontend', 'قواعد البيانات', 'الخوادم/DevOps', 'تدريب المدربين']],
                ['name' => 'trainees_count', 'label' => 'عدد المتدربين', 'type' => 'text'],
            ],
            'meta_title' => ['ar' => 'تدريب تقني للشركات والجهات — AI وبرمجة', 'en' => 'Tech training for companies & organizations — AI & coding'],
            'meta_description' => ['ar' => 'برامج تدريب تقني احترافية: ذكاء اصطناعي، Backend، Frontend، قواعد بيانات، وخوادم، مع تدريب المدربين للجهات والشركات.', 'en' => 'Professional tech training: AI, Backend, Frontend, Databases and Servers, plus training of trainers for organizations and companies.'],
            'sort_order' => 3,
            'is_active' => true,
        ]);
    }

    private function seedPages(): void
    {
        Page::updateOrCreate(['slug' => 'about'], [
            'title' => ['ar' => 'من نحن', 'en' => 'About us'],
            'status' => 'published',
            'excerpt' => ['ar' => 'وريد منصتك التقنية المتكاملة — نمكّن رواد الأعمال والشركات والجهات بأدوات وحلول تقنية عالمية المستوى.', 'en' => 'Wareed is your all-in-one tech platform — empowering entrepreneurs, companies and organizations with world-class tools and solutions.'],
            'content' => [
                ['type' => 'richtext', 'data' => ['html' => '<p>تأسست <strong>وريد</strong> برؤية واضحة: أن نكون شريكك التقني الأول. نجمع تحت سقف واحد ثلاث خدمات متكاملة — المتاجر الإلكترونية، الحلول التقنية المؤسسية، والتدريب التقني.</p>']],
                ['type' => 'stats', 'data' => ['items' => [
                    ['value' => '+500', 'label' => 'عميل يثق بنا'],
                    ['value' => '3', 'label' => 'خدمات متكاملة'],
                    ['value' => '99%', 'label' => 'جاهزية التشغيل'],
                    ['value' => '24/7', 'label' => 'دعم متواصل'],
                ]]],
                ['type' => 'cta', 'data' => ['title' => 'انضم لرحلة وريد', 'subtitle' => 'لنبنِ مستقبلك التقني معاً.', 'button_label' => 'تواصل معنا', 'button_url' => '/contact']],
            ],
            'meta_title' => ['ar' => 'من نحن — وريد', 'en' => 'About — Wareed'],
            'meta_description' => ['ar' => 'تعرّف على وريد، شريكك التقني الأول: متاجر إلكترونية، حلول تقنية، وتدريب احترافي.', 'en' => 'Learn about Wareed, your leading tech partner: online stores, tech solutions, and professional training.'],
            'is_system' => true,
        ]);
    }

    private function seedDemoStore(): void
    {
        $store = Store::updateOrCreate(['slug' => 'noor-store'], [
            'name' => ['ar' => 'متجر نور', 'en' => 'Noor Store'],
            'tagline' => ['ar' => 'أزياء وإكسسوارات عصرية', 'en' => 'Modern fashion & accessories'],
            'description' => ['ar' => 'متجر نور للأزياء العصرية والإكسسوارات — جودة عالية وأسعار مناسبة، توصيل سريع مع الدفع عند الاستلام.', 'en' => 'Noor Store for modern fashion and accessories — high quality at great prices, fast delivery with Cash on Delivery.'],
            'primary_color' => '#8B5CF6',
            'whatsapp' => '201001234567',
            'currency' => 'USD',
            'plan' => 'growth',
            'status' => 'active',
            'meta_title' => ['ar' => 'متجر نور — أزياء وإكسسوارات', 'en' => 'Noor Store — fashion & accessories'],
            'meta_description' => ['ar' => 'تسوّق أحدث الأزياء والإكسسوارات من متجر نور، توصيل سريع والدفع عند الاستلام.', 'en' => 'Shop the latest fashion and accessories from Noor Store, fast delivery with Cash on Delivery.'],
        ]);

        app()->instance('currentStoreId', $store->id);

        $cat = Category::updateOrCreate(['store_id' => $store->id, 'slug' => 'fashion'], [
            'name' => ['ar' => 'أزياء', 'en' => 'Fashion'], 'sort_order' => 1, 'is_active' => true,
        ]);

        $products = [
            [['ar' => 'قميص كاجوال قطن', 'en' => 'Cotton casual shirt'], 'shirt-casual', 45000, 60000],
            [['ar' => 'فستان سهرة أنيق', 'en' => 'Elegant evening dress'], 'evening-dress', 120000, 150000],
            [['ar' => 'حقيبة يد جلد', 'en' => 'Leather handbag'], 'leather-bag', 85000, null],
            [['ar' => 'ساعة يد كلاسيك', 'en' => 'Classic wristwatch'], 'classic-watch', 95000, 130000],
            [['ar' => 'نظارة شمسية', 'en' => 'Sunglasses'], 'sunglasses', 35000, 50000],
            [['ar' => 'حذاء رياضي', 'en' => 'Sneakers'], 'sneakers', 70000, 90000],
        ];

        foreach ($products as $i => [$name, $slug, $price, $compare]) {
            Product::updateOrCreate(['store_id' => $store->id, 'slug' => $slug], [
                'category_id' => $cat->id,
                'name' => $name,
                'description' => ['ar' => 'منتج عالي الجودة من متجر نور. خامات ممتازة وتشطيب راقٍ، مع ضمان الجودة وتوصيل سريع.', 'en' => 'High-quality product from Noor Store. Premium materials and fine finishing, with a quality guarantee and fast delivery.'],
                'price' => $price,
                'compare_price' => $compare,
                'stock' => 50,
                'is_active' => true,
                'is_featured' => $i < 3,
                'sort_order' => $i + 1,
            ]);
        }

        app()->forgetInstance('currentStoreId');
    }
}
