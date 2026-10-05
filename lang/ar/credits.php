<?php

declare(strict_types=1);

return [

    'title'           => 'النقاط',
    'balance_label'   => 'رصيدكم',
    'balance_unit'    => 'نقاط متاحة',
    'balance_tooltip' => 'نقطة واحدة = فتح طلب واحد',
    'packs_title'     => 'شحن رصيدي',
    'pack_per_unlock' => '⁦:price⁩ دج للنقطة',
    'pack_popular'    => 'الأكثر طلبًا',
    'pack_cta'        => 'شراء',

    'recent_title'    => 'آخر العمليات',
    'full_history'    => 'عرض السجل كاملًا',
    'no_transactions' => 'لا توجد عمليات حاليًا.',

    'history_title'   => 'سجل العمليات',
    'history_export'  => 'تصدير بصيغة CSV',
    'history_filter'  => 'النوع',
    'history_all'     => 'الكل',

    'type' => [
        'purchase'         => 'شراء',
        'unlock'           => 'فتح طلب',
        'refund'           => 'استرجاع',
        'bonus'            => 'هدية',
        'admin_adjustment' => 'تعديل من الإدارة',
    ],

    'success_title'  => 'تم الدفع بنجاح 🎉',
    'success_sub'    => 'أُضيفت النقاط إلى حسابكم.',
    'success_cta'    => 'عرض الطلبات',
    'success_pdf'    => 'تحميل الوصل',
    'success_pending_title' => 'الدفع قيد المعالجة…',
    'success_pending_sub'   => 'لا تغلقوا هذه الصفحة. ستكون نقاطكم متاحة بعد لحظات.',

    'failure_title'   => 'لم يكتمل الدفع',
    'failure_sub'     => 'لم تتم عملية الدفع.',
    'failure_reason'  => 'السبب',
    'failure_retry'   => 'إعادة المحاولة',
    'failure_contact' => 'اتصلوا بنا',
    'failure_tips'    => [
        'funds'  => 'رصيد بطاقتكم غير كافٍ.',
        'limit'  => 'تم بلوغ سقف الدفع.',
        'refuse' => 'رفض بنككم العملية.',
        'retry'  => 'جرّبوا بطاقة أخرى، أو اختاروا الدفع بالتحويل.',
    ],

    'checkout' => [
        'failed'       => 'تعذّر بدء عملية الدفع. يرجى إعادة المحاولة.',
        'no_url'       => 'ردّ غير متوقع من خدمة الدفع.',
        'not_verified' => 'يجب أن يوثّق فريقنا حسابكم قبل أن تتمكنوا من شراء النقاط.',
        'no_method'    => 'لا توجد وسيلة دفع متاحة حاليًا. يرجى الاتصال بفريقنا.',
    ],

    'offline' => [
        'submitted' => 'تم تسجيل تصريحكم بالتحويل. ستُضاف النقاط إلى حسابكم فور تأكيده من فريقنا.',
    ],

    'receipt' => [
        'title'        => 'وصل الدفع',
        'download_pdf' => 'تحميل بصيغة PDF',
        'issued_to'    => 'صادر لفائدة',
        'paid_on'      => 'تاريخ الدفع',
        'reference'    => 'المرجع',
        'gateway_ref'  => 'مرجع Chargily',
        'status'       => 'الحالة',
        'amount'       => 'المبلغ',
        'unlocks'      => 'نقاط فتح الطلبات',
        'footer'       => 'Zawaj Pro · منصة الأعراس في الجزائر. مقاول ذاتي. دفع آمن عبر Chargily Pay.',
    ],

    'low_balance_warning' => '⚠️ نقاطكم غير كافية لقبول طلب. اشحنوا رصيدكم.',
    'insufficient_redirect' => 'نقاطكم غير كافية. اشتروا باقة لقبول هذا الطلب.',
];
