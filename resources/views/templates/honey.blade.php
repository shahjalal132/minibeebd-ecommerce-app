<!DOCTYPE html>
<html lang="bn">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MiniBee Honey</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@100..900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        honey: '#FFC107',
                        honeyDark: '#E0A800',
                        olive: '#6D8C3C',
                        creamWarm: '#FFF3CD',
                        earth: '#5D4037'
                    },
                    fontFamily: {
                        'bengali': ['Noto Sans Bengali', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <style>
        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Noto Sans Bengali', sans-serif;
        }

        .honeycomb-pattern {
            background-image:
                radial-gradient(circle at 25% 25%, #FFC107 2px, transparent 2px),
                radial-gradient(circle at 75% 75%, #FFC107 2px, transparent 2px);
            background-size: 50px 50px;
            background-position: 0 0, 25px 25px;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fadeInUp {
            animation: fadeInUp 0.6s ease-out forwards;
        }

        .delay-100 {
            animation-delay: 0.1s;
        }

        .delay-200 {
            animation-delay: 0.2s;
        }

        .delay-300 {
            animation-delay: 0.3s;
        }

        .delay-400 {
            animation-delay: 0.4s;
        }

        .delay-500 {
            animation-delay: 0.5s;
        }

        .delay-600 {
            animation-delay: 0.6s;
        }

        .delay-700 {
            animation-delay: 0.7s;
        }

        @keyframes pulse-slow {

            0%,
            100% {
                opacity: 0.3;
            }

            50% {
                opacity: 0.6;
            }
        }

        .animate-pulse-slow {
            animation: pulse-slow 3s ease-in-out infinite;
        }

        @keyframes fadeIn {
            0% {
                opacity: 0;
                transform: translateY(20px);
            }

            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fadeIn {
            animation: fadeIn 0.6s ease-out forwards;
        }

        .ornate-border {
            border: 3px solid #8B6914;
            border-radius: 20px;
            position: relative;
            background: linear-gradient(135deg, #D4A574 0%, #C9A36B 50%, #D4A574 100%);
            padding: 3px;
        }

        @keyframes cross-through {
            0% {
                width: 0;
            }

            100% {
                width: 100%;
            }
        }

        .price-cross {
            position: relative;
            display: inline-block;
            color: #9ca3af;
            /* muted gray */
            font-size: 2.5rem;
            margin-right: 1.5rem;
        }

        .price-cross::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            width: 100%;
            height: 3px;
            background-color: #ef4444;
            /* red */
            transform: translateY(-50%) rotate(-15deg);
            transform-origin: center;
            animation: strikeAnimation 2s ease-in-out infinite;
        }

        @keyframes strikeAnimation {

            0%,
            100% {
                transform: translateY(-50%) rotate(-15deg) scaleX(1);
                opacity: 0.8;
            }

            50% {
                transform: translateY(-50%) rotate(-15deg) scaleX(1.1);
                opacity: 1;
            }
        }

        @keyframes underline-grow {

            0%,
            100% {
                width: 100%;
                opacity: 1;
            }

            50% {
                width: 80%;
                opacity: 0.7;
            }
        }

        @keyframes text-pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }

        .price-offer {
            position: relative;
            display: inline-block;
            font-size: 3.5rem;
            font-weight: bold;
            animation: text-pulse 2s ease-in-out infinite;
        }

        .price-offer::after {
            content: '';
            position: absolute;
            bottom: 5px;
            left: 0;
            width: 100%;
            height: 3px;
            background-color: #22c55e;
            /* green */
            animation: underline-grow 2s ease-in-out infinite;
        }

        position: absolute;
        width: 40px;
        height: 40px;
        border: 3px solid #8B6914;
        }

        .ornate-corner.top-left {
            top: -3px;
            left: -3px;
            border-right: none;
            border-bottom: none;
            border-top-left-radius: 20px;
        }

        .ornate-corner.top-right {
            top: -3px;
            right: -3px;
            border-left: none;
            border-bottom: none;
            border-top-right-radius: 20px;
        }

        .ornate-corner.bottom-left {
            bottom: -3px;
            left: -3px;
            border-right: none;
            border-top: none;
            border-bottom-left-radius: 20px;
        }

        .ornate-corner.bottom-right {
            bottom: -3px;
            right: -3px;
            border-left: none;
            border-top: none;
            border-bottom-right-radius: 20px;
        }

        .question-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #8B6914, #6B5310);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: bold;
            color: #D4A574;
            border: 2px solid #D4A574;
            position: relative;
            z-index: 12;
        }

        /* Toast Notification Styles */
        .toast-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            min-width: 320px;
            max-width: 400px;
            padding: 20px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: center;
            gap: 16px;
            animation: slideInRight 0.4s ease-out;
            font-family: 'Noto Sans Bengali', sans-serif;
        }

        .toast-success {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }

        .toast-error {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
        }

        .toast-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
        }

        .toast-content {
            flex: 1;
        }

        .toast-title {
            font-weight: 700;
            font-size: 18px;
            margin-bottom: 4px;
        }

        .toast-message {
            font-size: 14px;
            opacity: 0.95;
            line-height: 1.5;
        }

        .toast-close {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            transition: background 0.2s;
            flex-shrink: 0;
        }

        .toast-close:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }

            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }

        .toast-notification.hiding {
            animation: slideOutRight 0.3s ease-in forwards;
        }
    </style>

    @if (setting('fb_pixel_id'))
        <!-- Meta Pixel Code -->
        <script>
            ! function(f, b, e, v, n, t, s) {
                if (f.fbq) return;
                n = f.fbq = function() {
                    n.callMethod ?
                        n.callMethod.apply(n, arguments) : n.queue.push(arguments)
                };
                if (!f._fbq) f._fbq = n;
                n.push = n;
                n.loaded = !0;
                n.version = '2.0';
                n.queue = [];
                t = b.createElement(e);
                t.async = !0;
                t.src = v;
                s = b.getElementsByTagName(e)[0];
                s.parentNode.insertBefore(t, s)
            }(window, document, 'script',
                'https://connect.facebook.net/en_US/fbevents.js');

            fbq('init', '{{ setting('fb_pixel_id') }}');
            fbq('track', 'PageView');
        </script>

        <noscript>
            <img height="1" width="1" style="display:none"
                src="https://www.facebook.com/tr?id={{ setting('fb_pixel_id') }}&ev=PageView&noscript=1" />
        </noscript>
        <!-- End Meta Pixel Code -->
    @endif
</head>

<body class="bg-creamWarm text-earth">

    <!-- Hero Section -->
    <section class="flex items-center justify-center px-6 md:pt-20 pb-10 bg-gradient-to-b from-yellow-100 to-amber-50">
        <div class="max-w-5xl text-center">
            @if ($honeyPage && isset($honeyPage->content['hero']['heading']))
                <h1
                    class="font-bold relative bg-white mt-10 md:mt-0 py-4 px-8 rounded-2xl text-xl md:text-2xl leading-relaxed mb-12">
                    {!! $honeyPage->content['hero']['heading'] !!}
                </h1>
            @else
                <h1
                    class="font-bold relative bg-white mt-10 md:mt-0 py-4 px-8 rounded-2xl text-xl md:text-2xl leading-relaxed mb-12">
                    "তাদের উদর থেকে বের হয় বিভিন্ন রঙের পানীয় এতে মানুষের জন্য রয়েছে আরোগ্য।" <br>
                    <span class="">- সূরা নাহল</span>
                </h1>
            @endif

            <ul class="text-left space-y-4 text-xl mb-12">
                @if ($honeyPage && isset($honeyPage->content['hero']['questions']) && is_array($honeyPage->content['hero']['questions']))
                    @foreach ($honeyPage->content['hero']['questions'] as $question)
                        @if (!empty($question))
                            <li class="flex items-start gap-3">
                                <img src="/icons/arrow-right.png" alt="arrow" class="w-6 h-6 mt-1 flex-shrink-0">
                                <span>{{ $question }}</span>
                            </li>
                        @endif
                    @endforeach
                @else
                    <li class="flex items-start gap-3">
                        <img src="/icons/arrow-right.png" alt="arrow" class="w-6 h-6 mt-1 flex-shrink-0">
                        <span>আপনি কি আপনার পরিবারকে 'সাদা বিষ' খ্যাত চিনি থেকে মুক্ত রাখার সমাধান খুঁজছেন?</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <img src="/icons/arrow-right.png" alt="arrow" class="w-6 h-6 mt-1 flex-shrink-0">
                        <span>আপনি কি সুন্দরবনের ১০০% বিশুদ্ধ নির্ভেজাল চিনিমুক্ত মধু খুঁজছেন?</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <img src="/icons/arrow-right.png" alt="arrow" class="w-6 h-6 mt-1 flex-shrink-0">
                        <span>আপনি কি সহজে বহনযোগ্য, ঝামেলাহীন প্যাকেটে মধু খুঁজছেন?</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <img src="/icons/arrow-right.png" alt="arrow" class="w-6 h-6 mt-1 flex-shrink-0">
                        <span>আপনি কি স্বাস্থ্যসম্মত ফুড গ্রেড প্যাকেটে মধু খুঁজছেন যা আপনি নির্দ্বিধায় গ্রহণ করতে
                            পারবেন?</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <img src="/icons/arrow-right.png" alt="arrow" class="w-6 h-6 mt-1 flex-shrink-0">
                        <span>আপনি কি মধুকে ঔষধ হিসেবে গ্রহণ করতে চান?</span>
                    </li>
                @endif
            </ul>

            {{-- Welcome section --}}
            <div>
                @if ($honeyPage && isset($honeyPage->content['welcome']['message']))
                    <p class="text-xl md:text-3xl font-bold bg-white py-4 px-8 rounded-2xl mb-8">
                        {{ $honeyPage->content['welcome']['message'] }}</p>
                @else
                    <p class="text-xl md:text-3xl font-bold bg-white py-4 px-8 rounded-2xl mb-8">"উপরের প্রশ্নগুলোর
                        উত্তর যদি
                        'হ্যা' হয়ে থাকে তবে আপনি সঠিক স্থানেই এসেছেন"</p>
                @endif

                @if ($honeyPage && isset($honeyPage->content['welcome']['heading']))
                    <h2 class="text-5xl md:text-6xl font-bold mb-6">{{ $honeyPage->content['welcome']['heading'] }}</h2>
                @else
                    <h2 class="text-5xl md:text-6xl font-bold mb-6">Welcome to Mini Bee</h2>
                @endif

                <div class="flex justify-center mb-10">
                    @if ($honeyPage && isset($honeyPage->content['welcome']['logo']) && $honeyPage->content['welcome']['logo'])
                        <img src="{{ asset($honeyPage->content['welcome']['logo']) }}" alt="Logo"
                            class="w-40 h-40 rounded-full object-contain">
                    @else
                        <img src="https://minibeebd.com/images/bee-logo.jpeg" alt="MiniBee Logo"
                            class="w-40 h-40 rounded-full object-contain">
                    @endif
                </div>
            </div>

            {{-- Description --}}
            <div class="max-w-4xl mx-auto text-center">
                @if ($honeyPage && isset($honeyPage->content['description']) && $honeyPage->content['description'])
                    <p class="text-xl md:text-2xl leading-relaxed mb-4">
                        {{ $honeyPage->content['description'] }}
                    </p>
                @else
                    <p class="text-xl md:text-2xl leading-relaxed mb-4">
                        আপনার সব সমস্যার সমাধান দিতেই MiniBee নিয়ে এসেছে ৪ গ্রাম এর ৫০টি ছোট স্যাচেট এর সমন্বয়ে এক
                        প্রিমিয়াম
                        বক্স। এতে পাবেন সুন্দরবনের খাঁটি, নির্ভেজাল, প্রাকৃতিক চাকের মধু। এটি সহজেই আপনার পকেটে রাখতে
                        পারবেন
                        আর
                        যেখানে ইচ্ছা সেখানেই চিনির পরিবর্তে গ্রহণ করতে পারবেন। নিশ্চিন্তে আপনার সোনামণির হাতে তুলে দিতে
                        পারবেন
                        চকলেট বা ক্ষতিকর খাদ্যের বিকল্প হিসেবে।
                    </p>
                @endif
            </div>

            {{-- Order now --}}
            <div class="text-center mb-6 mt-12">
                <a href="#checkout"
                class="bg-honey hover:bg-honeyDark text-white px-10 py-5 rounded-2xl shadow-lg text-xl font-semibold">অর্ডার
                করুন</a>
            </div>

        </div>
    </section>

    <!-- Why Buy Section -->
    <section class="py-20 px-6 bg-white">
        <h2 class="text-4xl md:text-5xl font-bold text-center mb-16">কেন আমাদের মধু কিনবেন?</h2>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8 max-w-6xl mx-auto">
            <!-- Left Column - Top Cards -->
            <div class="space-y-6">
                @if ($honeyPage && isset($honeyPage->content['why_buy']['cards']) && is_array($honeyPage->content['why_buy']['cards']))
                    @foreach (array_slice($honeyPage->content['why_buy']['cards'], 0, 2) as $card)
                        <div class="bg-white p-8 rounded-2xl shadow">
                            <div class="flex items-start gap-4">
                                @if (isset($card['icon']) && $card['icon'])
                                    <div class="flex-shrink-0">
                                        <img src="{{ asset($card['icon']) }}" alt="Icon" class="w-12 h-12">
                                    </div>
                                @endif
                                <div class="flex-1">
                                    @if (isset($card['heading']))
                                        <h3 class="text-xl font-bold mb-2">{{ $card['heading'] }}</h3>
                                    @endif
                                    @if (isset($card['description']))
                                        <p class="text-lg">{{ $card['description'] }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <!-- Default cards if no data -->
                    <div class="bg-white p-8 rounded-2xl shadow">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                <img src="/icons/natural.png" alt="Natural Icon" class="w-12 h-12">
                            </div>
                            <div class="flex-1">
                                <h3 class="text-xl font-bold mb-2">শুদ্ধতা ও প্রাকৃতিকতাঃ</h3>
                                <p class="text-lg">আমাকদের মধু ১০০% প্রাকৃতিক সুন্দরবনের মধু যা সংগ্রহ করা হয়েছে
                                    সুন্দরবনের মৌয়ালদের থেকে।</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white p-8 rounded-2xl shadow">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                <img src="/icons/credit-card.png" alt="Card Icon" class="w-12 h-12">
                            </div>
                            <div class="flex-1">
                                <h3 class="text-xl font-bold mb-2">সহজে বহনযোগ্যঃ</h3>
                                <p class="text-lg">আমাদের একটি বক্সে ৪৪ এর ৫০ টি প্যাকেট রয়েছে যা সহজে আপনি আপনার
                                    প্রয়োজনমত যেখানে ইচ্ছা বহন ও খেতে পারেন।</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Middle Column - Product Image -->
            <div class="flex items-center justify-center lg:col-span-1">
                <div class="relative">
                    @if (
                        $honeyPage &&
                            isset($honeyPage->content['why_buy']['center_image']) &&
                            $honeyPage->content['why_buy']['center_image']
                    )
                        <img src="{{ asset($honeyPage->content['why_buy']['center_image']) }}" alt="Product Box"
                            class="max-w-full h-auto rounded-2xl shadow-lg" style="max-height: 500px;">
                    @else
                        <img src="https://assets.mediamodifier.com/mockups/6141d9209041e3ae08e25255/open-cardboard-product-box-mockup_thumb.jpg"
                            alt="Mini Bee Love Honey Box" class="max-w-full h-auto rounded-2xl shadow-lg"
                            style="max-height: 500px;">
                    @endif
                </div>
            </div>

            <!-- Right Column - Top Cards -->
            <div class="space-y-6">
                @if ($honeyPage && isset($honeyPage->content['why_buy']['cards']) && is_array($honeyPage->content['why_buy']['cards']))
                    @foreach (array_slice($honeyPage->content['why_buy']['cards'], 2, 2) as $card)
                        <div class="bg-white p-8 rounded-2xl shadow">
                            <div class="flex items-start gap-4">
                                @if (isset($card['icon']) && $card['icon'])
                                    <div class="flex-shrink-0">
                                        <img src="{{ asset($card['icon']) }}" alt="Icon" class="w-12 h-12">
                                    </div>
                                @endif
                                <div class="flex-1">
                                    @if (isset($card['heading']))
                                        <h3 class="text-xl font-bold mb-2">{{ $card['heading'] }}</h3>
                                    @endif
                                    @if (isset($card['description']))
                                        <p class="text-lg">{{ $card['description'] }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <!-- Default cards -->
                    <div class="bg-white p-8 rounded-2xl shadow">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                <img src="/icons/package.png" alt="Card Icon" class="w-12 h-12">
                            </div>
                            <div class="flex-1">
                                <h3 class="text-xl font-bold mb-2">স্বাস্থ্যসম্মত প্যাকেজিংঃ</h3>
                                <p class="text-lg">তিন স্তরের ফুড অ্যালুমিনিয়াম ফয়েল ব্যবহার করা হয়েছে যা দীর্ঘদিন
                                    মধুকে রাখে সতেজ, তাজা এবং স্বাস্থ্যসম্মত।</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white p-8 rounded-2xl shadow">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                <img src="/icons/weighing-machine.png" alt="Weight Icon" class="w-12 h-12">
                            </div>
                            <div class="flex-1">
                                <h3 class="text-xl font-bold mb-2">পরিমিত গ্রহণ ও অপচয় রোধঃ</h3>
                                <p class="text-lg">আমাদের মধু ৪৭ এর ছোট প্যাকেট এ থাকায় মধু বেশি পড়ে গিয়ে অপচয়
                                    হওয়ার সুযোগ নেই, অপরদিকে পরিমিত গ্রহণ নিশ্চিত করবে।</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Bottom Card -->
        <div class="flex justify-center mt-8">
            @if ($honeyPage && isset($honeyPage->content['why_buy']['cards']) && count($honeyPage->content['why_buy']['cards']) > 4)
                <div class="bg-white p-8 rounded-2xl shadow max-w-lg">
                    <div class="flex items-start gap-4">
                        @if (isset($honeyPage->content['why_buy']['cards'][4]['icon']) && $honeyPage->content['why_buy']['cards'][4]['icon'])
                            <div class="flex-shrink-0">
                                <img src="{{ asset($honeyPage->content['why_buy']['cards'][4]['icon']) }}"
                                    alt="Icon" class="w-12 h-12">
                            </div>
                        @endif
                        <div class="flex-1">
                            @if (isset($honeyPage->content['why_buy']['cards'][4]['heading']))
                                <h3 class="text-xl font-bold mb-2">
                                    {{ $honeyPage->content['why_buy']['cards'][4]['heading'] }}</h3>
                            @endif
                            @if (isset($honeyPage->content['why_buy']['cards'][4]['description']))
                                <p class="text-lg">{{ $honeyPage->content['why_buy']['cards'][4]['description'] }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-white p-8 rounded-2xl shadow max-w-lg">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <img src="/icons/no-chocolate.png" alt="No Chocolate Icon" class="w-12 h-12">
                        </div>
                        <div class="flex-1">
                            <h3 class="text-xl font-bold mb-2">চকলেট ও ক্ষতিকর খাদ্যের বিকল্পঃ</h3>
                            <p class="text-lg">আপনার সোনামণির স্কুলব্যাগে দিয়ে দিতে পারবেন খুব সহজেই যা আপনার
                                সোনামনিকে চকলেটের মত ক্ষতিকর জিনিস থেকে দুরে রাখবে।</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="text-center mt-16">
            <a href="#checkout"
                class="bg-honey hover:bg-honeyDark text-white px-10 py-5 rounded-2xl shadow-lg text-xl font-semibold">অর্ডার
                করুন</a>
        </div>
    </section>

    <!-- Why Eat Honey -->
    <section class="py-20 px-6 bg-amber-50 relative overflow-hidden">
        <h2 class="text-4xl md:text-5xl font-bold text-center mb-12 relative z-10">মধু কেন খাবেন</h2>

        <div class="absolute top-0 right-0 w-96 h-96 -mt-24 -mr-24 honeycomb-pattern opacity-30 pointer-events-none">
        </div>
        <div class="absolute bottom-0 left-0 w-96 h-96 -mb-24 -ml-24 honeycomb-pattern opacity-30 pointer-events-none">
        </div>

        <div class="max-w-7xl mx-auto relative z-10">
            @if (
                $honeyPage &&
                    isset($honeyPage->content['why_eat_honey']['cards']) &&
                    is_array($honeyPage->content['why_eat_honey']['cards']) &&
                    count($honeyPage->content['why_eat_honey']['cards']) > 0)
                @php
                    $cards = $honeyPage->content['why_eat_honey']['cards'];
                    $firstRow = array_slice($cards, 0, 4);
                    $secondRow = array_slice($cards, 4);
                @endphp

                <!-- Benefits Grid - First Row (4 columns) -->
                @if (count($firstRow) > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-4 md:mb-6">
                        @foreach ($firstRow as $index => $card)
                            <div
                                class="opacity-0 animate-fadeInUp delay-{{ ($index + 1) * 100 }} bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-3xl p-6 shadow-xl hover:shadow-2xl transition-all duration-500 hover:-translate-y-3 hover:scale-105 relative border-4 border-yellow-400 group">
                                <div
                                    class="absolute inset-0 bg-gradient-to-br from-yellow-200/20 to-yellow-300/20 rounded-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                                </div>

                                <div
                                    class="relative w-20 h-20 md:w-24 md:h-24 mx-auto mb-4 flex items-center justify-center bg-gradient-to-br from-white to-yellow-50 rounded-full border-4 border-yellow-400 shadow-lg group-hover:shadow-xl transition-all duration-500 group-hover:rotate-12 group-hover:scale-110">
                                    <div
                                        class="absolute inset-0 bg-gradient-to-br from-yellow-200/30 to-yellow-300/30 rounded-full animate-pulse-slow">
                                    </div>
                                    @if (isset($card['icon']) && $card['icon'])
                                        <img src="{{ asset($card['icon']) }}" alt="Icon"
                                            class="w-12 h-12 md:w-14 md:h-14 relative z-10">
                                    @endif
                                </div>

                                @if (isset($card['title']))
                                    <h3
                                        class="text-gray-900 font-bold text-lg md:text-xl text-center mb-3 leading-tight relative">
                                        {{ $card['title'] }}</h3>
                                @endif
                                @if (isset($card['description']))
                                    <p class="text-gray-700 text-sm md:text-base text-center leading-relaxed relative">
                                        {{ $card['description'] }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- Second Row -->
                @if (count($secondRow) > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 justify-center md:gap-6">
                        @foreach ($secondRow as $index => $card)
                            <div
                                class="opacity-0 animate-fadeInUp delay-{{ ($index + 5) * 100 }} bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-3xl p-6 shadow-xl hover:shadow-2xl transition-all duration-500 hover:-translate-y-3 hover:scale-105 relative border-4 border-yellow-400 group">
                                <div
                                    class="absolute inset-0 bg-gradient-to-br from-yellow-200/20 to-yellow-300/20 rounded-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                                </div>

                                <div
                                    class="relative w-20 h-20 md:w-24 md:h-24 mx-auto mb-4 flex items-center justify-center bg-gradient-to-br from-white to-yellow-50 rounded-full border-4 border-yellow-400 shadow-lg group-hover:shadow-xl transition-all duration-500 group-hover:rotate-12 group-hover:scale-110">
                                    <div
                                        class="absolute inset-0 bg-gradient-to-br from-yellow-200/30 to-yellow-300/30 rounded-full animate-pulse-slow">
                                    </div>
                                    @if (isset($card['icon']) && $card['icon'])
                                        <img src="{{ asset($card['icon']) }}" alt="Icon"
                                            class="w-12 h-12 md:w-14 md:h-14 relative z-10">
                                    @endif
                                </div>

                                @if (isset($card['title']))
                                    <h3
                                        class="text-gray-900 font-bold text-lg md:text-xl text-center mb-3 leading-tight relative">
                                        {{ $card['title'] }}</h3>
                                @endif
                                @if (isset($card['description']))
                                    <p class="text-gray-700 text-sm md:text-base text-center leading-relaxed relative">
                                        {{ $card['description'] }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            @else
                <!-- Default static cards if no data -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-4 md:mb-6">
                    <div
                        class="opacity-0 animate-fadeInUp delay-100 bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-3xl p-6 shadow-xl hover:shadow-2xl transition-all duration-500 hover:-translate-y-3 hover:scale-105 relative border-4 border-yellow-400 group">
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-yellow-200/20 to-yellow-300/20 rounded-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                        </div>
                        <div
                            class="relative w-20 h-20 md:w-24 md:h-24 mx-auto mb-4 flex items-center justify-center bg-gradient-to-br from-white to-yellow-50 rounded-full border-4 border-yellow-400 shadow-lg group-hover:shadow-xl transition-all duration-500 group-hover:rotate-12 group-hover:scale-110">
                            <div
                                class="absolute inset-0 bg-gradient-to-br from-yellow-200/30 to-yellow-300/30 rounded-full animate-pulse-slow">
                            </div>
                            <img src="/icons/natural-power-source.png" alt="Natural Power Source"
                                class="w-12 h-12 md:w-14 md:h-14 relative z-10">
                        </div>
                        <h3 class="text-gray-900 font-bold text-lg md:text-xl text-center mb-3 leading-tight relative">
                            প্রাকৃতিক শক্তির উৎসঃ</h3>
                        <p class="text-gray-700 text-sm md:text-base text-center leading-relaxed relative">মধু শরীরে
                            শক্তি যোগায় এবং ক্লান্তি দূর করে।</p>
                    </div>
                    <!-- More default cards... -->
                </div>
            @endif
        </div>
    </section>

    <!-- Customer Reviews -->
    <section class="py-20 px-6 bg-white">
        <h2 class="text-4xl md:text-5xl font-bold text-center mb-12">গ্রাহকদের মতামত</h2>
        <div class="max-w-6xl mx-auto">
            @if ($honeyPage && isset($honeyPage->content['reviews']['type']))
                @if (
                    $honeyPage->content['reviews']['type'] == 'screenshot' &&
                        isset($honeyPage->content['reviews']['screenshot']) &&
                        $honeyPage->content['reviews']['screenshot']
                )
                    <!-- Screenshot Display -->
                    <div class="flex justify-center">
                        <img src="{{ asset($honeyPage->content['reviews']['screenshot']) }}" alt="Reviews Screenshot"
                            class="max-w-full rounded-2xl shadow-lg">
                    </div>
                @elseif(
                    $honeyPage->content['reviews']['type'] == 'review' &&
                        isset($honeyPage->content['reviews']['review_cards']) &&
                        is_array($honeyPage->content['reviews']['review_cards']) &&
                        count($honeyPage->content['reviews']['review_cards']) > 0)
                    <!-- Review Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
                        @foreach ($honeyPage->content['reviews']['review_cards'] as $review)
                            <div
                                class="bg-gradient-to-br from-yellow-50 to-amber-50 rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border-2 border-yellow-200">
                                <div class="flex items-center mb-4">
                                    @if (isset($review['avatar']) && $review['avatar'])
                                        <div
                                            class="w-12 h-12 bg-gradient-to-br from-yellow-400 to-amber-500 rounded-full flex items-center justify-center text-white font-bold text-xl mr-4 overflow-hidden">
                                            <img src="{{ asset($review['avatar']) }}" alt="Avatar"
                                                class="w-full h-full object-cover">
                                        </div>
                                    @else
                                        <div
                                            class="w-12 h-12 bg-gradient-to-br from-yellow-400 to-amber-500 rounded-full flex items-center justify-center text-white font-bold text-xl mr-4">
                                            {{ mb_substr($review['name'] ?? 'U', 0, 1) }}
                                        </div>
                                    @endif
                                    <div>
                                        @if (isset($review['name']))
                                            <h4 class="font-bold text-lg text-gray-900">{{ $review['name'] }}</h4>
                                        @endif
                                        @if (isset($review['rating']))
                                            <div class="flex text-yellow-400">
                                                @for ($i = 0; $i < min(5, (int) $review['rating']); $i++)
                                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                        <path
                                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                                        </path>
                                                    </svg>
                                                @endfor
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                @if (isset($review['details']))
                                    <p class="text-gray-700 text-base leading-relaxed">{{ $review['details'] }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            @else
                <!-- Default Reviews -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
                    <!-- Review Card 1 -->
                    <div
                        class="bg-gradient-to-br from-yellow-50 to-amber-50 rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border-2 border-yellow-200">
                        <div class="flex items-center mb-4">
                            <div
                                class="w-12 h-12 bg-gradient-to-br from-yellow-400 to-amber-500 rounded-full flex items-center justify-center text-white font-bold text-xl mr-4">
                                র
                            </div>
                            <div>
                                <h4 class="font-bold text-lg text-gray-900">রহিম উদ্দিন</h4>
                                <div class="flex text-yellow-400">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                        </path>
                                    </svg>
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                        </path>
                                    </svg>
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                        </path>
                                    </svg>
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                        </path>
                                    </svg>
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                        </path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <p class="text-gray-700 text-base leading-relaxed">খুবই ভালো মধু পেয়েছি। সুন্দরবনের খাঁটি মধু।
                            প্যাকেজিংও খুব সুন্দর। প্রতিদিন সকালে খাচ্ছি, শরীরে শক্তি পাচ্ছি।</p>
                    </div>

                    <!-- Review Card 2 -->
                    <div
                        class="bg-gradient-to-br from-yellow-50 to-amber-50 rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border-2 border-yellow-200">
                        <div class="flex items-center mb-4">
                            <div
                                class="w-12 h-12 bg-gradient-to-br from-yellow-400 to-amber-500 rounded-full flex items-center justify-center text-white font-bold text-xl mr-4">
                                ফ
                            </div>
                            <div>
                                <h4 class="font-bold text-lg text-gray-900">ফাতেমা খাতুন</h4>
                                <div class="flex text-yellow-400">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                        </path>
                                    </svg>
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                        </path>
                                    </svg>
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                        </path>
                                    </svg>
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                        </path>
                                    </svg>
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                        </path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <p class="text-gray-700 text-base leading-relaxed">ছোট প্যাকেটে থাকায় খুবই সুবিধা হয়েছে।
                            বাচ্চাদের স্কুলে দিতে পারছি। মধুও খুবই ভালো মানের।</p>
                    </div>

                    <!-- Review Card 3 -->
                    <div
                        class="bg-gradient-to-br from-yellow-50 to-amber-50 rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border-2 border-yellow-200">
                        <div class="flex items-center mb-4">
                            <div
                                class="w-12 h-12 bg-gradient-to-br from-yellow-400 to-amber-500 rounded-full flex items-center justify-center text-white font-bold text-xl mr-4">
                                ক
                            </div>
                            <div>
                                <h4 class="font-bold text-lg text-gray-900">কামাল হোসেন</h4>
                                <div class="flex text-yellow-400">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                        </path>
                                    </svg>
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                        </path>
                                    </svg>
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                        </path>
                                    </svg>
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                        </path>
                                    </svg>
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                        </path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <p class="text-gray-700 text-base leading-relaxed">মধুর মান খুবই ভালো। প্যাকেজিং দেখে বুঝতে
                            পারছি যে ফুড গ্রেড ব্যবহার করা হয়েছে। নির্ভেজাল মধু পেয়েছি।</p>
                    </div>
                </div>
            @endif
        </div>
    </section>

    <!-- FAQ -->
    <section class="py-20 px-6 bg-amber-50">
        <h2 class="text-4xl md:text-5xl font-bold text-center mb-12">সাধারণ জিজ্ঞাসা</h2>
        <div class="max-w-6xl mx-auto">
            <!-- FAQ Grid - 2x2 Layout -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8">
                @if (
                    $honeyPage &&
                        isset($honeyPage->content['faq']['items']) &&
                        is_array($honeyPage->content['faq']['items']) &&
                        count($honeyPage->content['faq']['items']) > 0)
                    @foreach ($honeyPage->content['faq']['items'] as $index => $item)
                        <div class="opacity-0 animate-fadeIn delay-{{ ($index + 1) * 100 }}">
                            <div class="ornate-border">
                                <div class="ornate-corner top-left"></div>
                                <div class="ornate-corner top-right"></div>
                                <div class="ornate-corner bottom-left"></div>
                                <div class="ornate-corner bottom-right"></div>

                                <div class="question-icon absolute -top-5 -right-3 md:-right-5">?</div>

                                <div
                                    class="bg-gradient-to-br from-yellow-100 to-yellow-50 rounded-2xl p-6 md:p-8 relative">
                                    @if (isset($item['question']))
                                        <h3 class="text-gray-900 font-bold text-xl md:text-2xl mb-4 leading-tight">
                                            Q. {{ $item['question'] }}
                                        </h3>
                                    @endif
                                    @if (isset($item['answer']))
                                        <div class="text-gray-800 text-base md:text-lg leading-relaxed">
                                            <span class="font-semibold text-gray-900">A→</span> {{ $item['answer'] }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <!-- Default FAQ items -->
                    <div class="opacity-0 animate-fadeIn delay-100">
                        <div class="ornate-border">
                            <div class="ornate-corner top-left"></div>
                            <div class="ornate-corner top-right"></div>
                            <div class="ornate-corner bottom-left"></div>
                            <div class="ornate-corner bottom-right"></div>
                            <div class="question-icon absolute -top-5 -right-3 md:-right-5">?</div>
                            <div
                                class="bg-gradient-to-br from-yellow-100 to-yellow-50 rounded-2xl p-6 md:p-8 relative">
                                <h3 class="text-gray-900 font-bold text-xl md:text-2xl mb-4 leading-tight">
                                    Q. মধু কিভাবে সেবন করব?
                                </h3>
                                <div class="text-gray-800 text-base md:text-lg leading-relaxed">
                                    <span class="font-semibold text-gray-900">A→</span> মধু খাওয়ার সঠিক নিয়ম নাই তবে
                                    ১০ গ্রাম - ২০ গ্রাম একজন পুর্নবয়স্ক লোকের জন্য সেবন করতে অনেকে পরামর্শ দেন। তবে মধু
                                    বেশি গ্রহণ করার ফলে কোন দুর্ঘটনা এখনো প্রমাণিত নয়। তবে মাত্রা-অতিরিক্ত সেবন না করাই
                                    উত্তম।
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section class="py-20 px-6 bg-white">
        <h2 class="text-4xl md:text-5xl font-bold text-center mb-12">প্রাইস</h2>
        <div class="max-w-6xl mx-auto">
            <div class="bg-gradient-to-r from-yellow-50 to-amber-50 border-2 border-honey p-10 rounded-2xl shadow-lg">
                <div class="grid md:grid-cols-2 gap-8 items-center">
                    <div class="text-center md:text-left">
                        <div class="mb-6">
                            @if ($honeyPage && isset($honeyPage->content['product']['image']) && $honeyPage->content['product']['image'])
                                <img src="{{ asset($honeyPage->content['product']['image']) }}" alt="Product Image"
                                    class="w-64 h-64 mx-auto md:mx-0 object-cover rounded-xl mb-6">
                            @endif
                        </div>
                    </div>
                    <div>
                        @if ($honeyPage && isset($honeyPage->content['product']['title']) && $honeyPage->content['product']['title'])
                            <h3 class="text-3xl font-bold mb-3">{{ $honeyPage->content['product']['title'] }}</h3>
                        @endif

                        @if ($honeyPage && isset($honeyPage->content['product']['quantity']) && $honeyPage->content['product']['quantity'])
                            <p class="text-xl mb-6">{{ $honeyPage->content['product']['quantity'] }}</p>
                        @endif

                        <div class="flex items-center justify-start mb-6">
                            <div class="flex flex-col items-center">
                                @php
                                    $regularPrice = 0;
                                    $offerPrice = 0;
                                    if ($honeyPage && isset($honeyPage->content['product']['regular_price'])) {
                                        $regularPrice = (float) ($honeyPage->content['product']['regular_price'] ?? 0);
                                    }
                                    if ($honeyPage && isset($honeyPage->content['product']['offer_price'])) {
                                        $offerPrice = (float) ($honeyPage->content['product']['offer_price'] ?? 0);
                                    }
                                    // If offer_price is not set, use regular_price as offer_price
                                    if ($offerPrice == 0 && $regularPrice > 0) {
                                        $offerPrice = $regularPrice;
                                    }
                                @endphp
                                @if ($regularPrice > 0 && $regularPrice > $offerPrice)
                                    <span class="price-cross">৳ {{ number_format($regularPrice, 0) }}</span>
                                @endif
                                @if ($offerPrice > 0)
                                    <span class="font-bold text-honey price-offer">৳
                                        {{ number_format($offerPrice, 0) }}</span>
                                @endif
                            </div>
                        </div>

                        @if (
                            $honeyPage &&
                                isset($honeyPage->content['product']['short_description']) &&
                                $honeyPage->content['product']['short_description']
                        )
                            <p class="text-lg mb-8">{{ $honeyPage->content['product']['short_description'] }}</p>
                        @endif

                        <a href="#checkout"
                            class="bg-honey hover:bg-honeyDark text-white px-8 py-4 rounded-xl text-lg font-semibold inline-block">অর্ডার
                            করুন</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Checkout -->
    <section id="checkout" class="py-20 px-6 bg-amber-50">
        <h2 class="text-4xl md:text-5xl font-bold text-center mb-12">চেকআউট</h2>
        <div class="max-w-6xl mx-auto grid md:grid-cols-2 gap-8">
            <!-- Order Form -->
            <div class="bg-white p-8 rounded-2xl shadow-lg">
                <h3 class="text-2xl font-bold mb-6">আপনার তথ্য</h3>
                <form class="space-y-5">
                    <input type="text" id="checkoutName" placeholder="নাম"
                        class="w-full p-4 border-2 rounded-xl text-lg focus:border-honey focus:outline-none">
                    <input type="text" id="checkoutPhone" placeholder="ফোন"
                        class="w-full p-4 border-2 rounded-xl text-lg focus:border-honey focus:outline-none">
                    <textarea id="checkoutAddress" placeholder="ঠিকানা" rows="4"
                        class="w-full p-4 border-2 rounded-xl text-lg focus:border-honey focus:outline-none"></textarea>

                    <!-- Shipping Charge -->
                    <div>
                        <p class="font-semibold mb-3 text-xl">শিপিং চার্জ</p>
                        <div class="space-y-3">
                            @foreach ($delivery_charges as $charge)
                                <label
                                    class="border-2 p-4 rounded-xl cursor-pointer flex items-center gap-3 hover:border-honey transition-colors">
                                    <input type="radio" name="shipping" value="{{ $charge->id }}"
                                        data-amount="{{ $charge->amount }}" data-delivery-charge-id="{{ $charge->id }}"
                                        class="accent-honey"
                                        onchange="updateTotal(); saveIncompleteOrder();">
                                    <span class="text-lg">{{ $charge->title }} – ৳ {{ $charge->amount }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div>
                        <p class="font-semibold mb-3 text-xl">পেমেন্ট পদ্ধতি</p>
                        <div class="space-y-3 mb-4">
                            <label
                                class="border-2 p-4 rounded-xl cursor-pointer flex items-center gap-3 hover:border-honey transition-colors">
                                <input type="radio" name="payment" value="cod" class="accent-honey" checked
                                    onchange="togglePaymentDetails(); saveIncompleteOrder();">
                                <span class="text-lg">ক্যাশ অন ডেলিভারি</span>
                            </label>

                            @foreach ($payment_methods as $method)
                                <label
                                    class="border-2 p-4 rounded-xl cursor-pointer flex items-center gap-3 hover:border-honey transition-colors">
                                    <input type="radio" name="payment" value="{{ $method->id }}"
                                        class="accent-honey" data-number="{{ $method->number }}"
                                        data-instruction="{{ $method->instruction }}"
                                        data-type="{{ $method->type }}" onchange="togglePaymentDetails(); saveIncompleteOrder();">
                                    <span class="text-lg">{{ $method->name }}</span>
                                </label>
                            @endforeach
                        </div>

                        <!-- Payment Details (Expandable) -->
                        <div id="paymentDetails" class="hidden mt-0 p-6 bg-gray-100 rounded-xl space-y-4">
                            <!-- Mobile Number Display -->
                            <div>
                                <p class="text-lg">
                                    <span class="text-honey font-semibold">মোবাইল নাম্বার: </span>
                                    <span id="agentNumber" class="text-honey font-semibold"></span>
                                </p>
                            </div>

                            <!-- Instruction Display -->
                            <div>
                                <p class="text-lg">
                                    <span class="font-semibold">নির্দেশনা: </span>
                                    <span id="paymentInstruction"></span>
                                </p>
                            </div>

                            <!-- Your Mobile Number Input -->
                            <div>
                                <input type="text" id="fromNumber" placeholder="আপনার মোবাইল নাম্বার *"
                                    class="w-full p-3 border-2 border-gray-300 rounded-xl text-lg focus:border-honey focus:outline-none bg-white">
                            </div>

                            <!-- Transaction ID Input -->
                            <div>
                                <input type="text" id="transactionId" placeholder="ট্রানজেকশন আইডি লিখুন *"
                                    class="w-full p-3 border-2 border-gray-300 rounded-xl text-lg focus:border-honey focus:outline-none bg-white">
                            </div>

                            <!-- Screenshot Upload -->
                            <div>
                                <input type="file" id="screenshot" accept="image/*"
                                    class="w-full p-3 border-2 border-gray-300 rounded-xl text-lg focus:border-honey focus:outline-none bg-white">
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Order Summary -->
            <div class="bg-white p-8 rounded-2xl md:h-[70%] shadow-lg">
                <h3 class="text-2xl font-bold mb-6">অর্ডার সারাংশ</h3>

                <!-- Product Info -->
                <div class="border-b-2 pb-6 mb-6">
                    <div class="flex gap-4 mb-4">
                        @if ($honeyPage && isset($honeyPage->content['product']['image']) && $honeyPage->content['product']['image'])
                            <img src="{{ asset($honeyPage->content['product']['image']) }}" alt="Product"
                                class="w-24 h-24 object-cover rounded-lg">
                        @endif
                        <div class="flex-1">
                            @if ($honeyPage && isset($honeyPage->content['product']['title']) && $honeyPage->content['product']['title'])
                                <h4 class="text-xl font-semibold mb-1">{{ $honeyPage->content['product']['title'] }}
                                </h4>
                            @endif

                            @if ($honeyPage && isset($honeyPage->content['product']['quantity']) && $honeyPage->content['product']['quantity'])
                                <p class="text-lg text-gray-600">{{ $honeyPage->content['product']['quantity'] }}</p>
                            @endif

                            @php
                                $productPrice = 0;
                                if (
                                    $honeyPage &&
                                    isset($honeyPage->content['product']['offer_price']) &&
                                    $honeyPage->content['product']['offer_price']
                                ) {
                                    $productPrice = $honeyPage->content['product']['offer_price'];
                                } elseif (
                                    $honeyPage &&
                                    isset($honeyPage->content['product']['regular_price']) &&
                                    $honeyPage->content['product']['regular_price']
                                ) {
                                    $productPrice = $honeyPage->content['product']['regular_price'];
                                }
                            @endphp
                            @if ($productPrice > 0)
                                <p class="text-xl font-bold text-honey mt-2">৳ {{ $productPrice }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Price Breakdown -->
                <div class="space-y-4 mb-6">
                    <div class="flex justify-between text-lg">
                        <span>পণ্যের মূল্য:</span>
                        <span class="font-semibold" id="productPrice">৳ {{ $productPrice }}</span>
                    </div>
                    <div class="flex justify-between text-lg">
                        <span>শিপিং চার্জ:</span>
                        <span class="font-semibold" id="shippingCharge">৳ 0</span>
                    </div>
                    <div class="flex justify-between text-lg pt-4 border-t-2">
                        <span>সাবটোটাল:</span>
                        <span class="font-semibold" id="subtotal">৳ {{ $productPrice }}</span>
                    </div>
                    <div class="flex justify-between text-2xl font-bold pt-4 border-t-2">
                        <span>মোট:</span>
                        <span class="text-honey" id="total">৳ {{ $productPrice }}</span>
                    </div>
                </div>

                <button onclick="placeOrder()"
                    class="w-full bg-honey hover:bg-honeyDark text-white py-4 rounded-xl text-xl font-semibold transition-colors">অর্ডার
                    করুন</button>
            </div>
        </div>
    </section>

    <script>
        // Debounce utility function (1.5 second delay)
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // Function to save incomplete order via AJAX
        const saveIncompleteOrder = debounce(function() {
            const name = document.getElementById('checkoutName')?.value || '';
            const phone = document.getElementById('checkoutPhone')?.value || '';
            const address = document.getElementById('checkoutAddress')?.value || '';
            const shipping = document.querySelector('input[name="shipping"]:checked');
            const payment = document.querySelector('input[name="payment"]:checked');
            const fromNumber = document.getElementById('fromNumber')?.value || '';
            const transactionId = document.getElementById('transactionId')?.value || '';
            const screenshot = document.getElementById('screenshot')?.files[0];

            // Only save if at least name or phone is filled
            if (!name && !phone) {
                return;
            }

            const formData = new FormData();
            formData.append('name', name);
            formData.append('phone', phone);
            formData.append('address', address);
            
            if (shipping) {
                formData.append('delivery_charge_id', shipping.getAttribute('data-delivery-charge-id'));
            }
            
            if (payment) {
                formData.append('payment_method', payment.value);
            }
            
            if (fromNumber) {
                formData.append('fromNumber', fromNumber);
            }
            
            if (transactionId) {
                formData.append('transactionId', transactionId);
            }
            
            if (screenshot) {
                formData.append('screenshot', screenshot);
            }

            formData.append('_token', '{{ csrf_token() }}');

            // Send AJAX request (fail silently)
            fetch('{{ route('front.honey.incomplete-order') }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).catch(error => {
                // Fail silently - don't interrupt user experience
                console.log('Failed to save incomplete order:', error);
            });
        }, 1500);

        // Add event listeners for form fields
        document.addEventListener('DOMContentLoaded', function() {
            const nameInput = document.getElementById('checkoutName');
            const phoneInput = document.getElementById('checkoutPhone');
            const addressInput = document.getElementById('checkoutAddress');
            const fromNumberInput = document.getElementById('fromNumber');
            const transactionIdInput = document.getElementById('transactionId');
            const screenshotInput = document.getElementById('screenshot');

            if (nameInput) {
                nameInput.addEventListener('keyup', saveIncompleteOrder);
            }
            if (phoneInput) {
                phoneInput.addEventListener('keyup', saveIncompleteOrder);
            }
            if (addressInput) {
                addressInput.addEventListener('keyup', saveIncompleteOrder);
            }
            if (fromNumberInput) {
                fromNumberInput.addEventListener('change', saveIncompleteOrder);
                fromNumberInput.addEventListener('keyup', saveIncompleteOrder);
            }
            if (transactionIdInput) {
                transactionIdInput.addEventListener('change', saveIncompleteOrder);
                transactionIdInput.addEventListener('keyup', saveIncompleteOrder);
            }
            if (screenshotInput) {
                screenshotInput.addEventListener('change', saveIncompleteOrder);
            }
        });

        function updateTotal() {
            @php
                $productPrice = 0;
                if ($honeyPage && isset($honeyPage->content['product']['offer_price']) && $honeyPage->content['product']['offer_price']) {
                    $productPrice = $honeyPage->content['product']['offer_price'];
                } elseif ($honeyPage && isset($honeyPage->content['product']['regular_price']) && $honeyPage->content['product']['regular_price']) {
                    $productPrice = $honeyPage->content['product']['regular_price'];
                }
            @endphp
            const productPrice = {{ $productPrice }};
            const shippingRadio = document.querySelector('input[name="shipping"]:checked');
            const shippingCharge = shippingRadio ? parseInt(shippingRadio.getAttribute('data-amount')) : 0;
            const subtotal = productPrice;
            const total = productPrice + shippingCharge;

            document.getElementById('shippingCharge').textContent = '৳ ' + shippingCharge;
            document.getElementById('subtotal').textContent = '৳ ' + subtotal;
            document.getElementById('total').textContent = '৳ ' + total;
        }

        function togglePaymentDetails() {
            const paymentMethod = document.querySelector('input[name="payment"]:checked');
            const paymentDetails = document.getElementById('paymentDetails');
            const agentNumber = document.getElementById('agentNumber');
            const paymentInstruction = document.getElementById('paymentInstruction');

            if (paymentMethod && paymentMethod.value !== 'cod') {
                paymentDetails.classList.remove('hidden');

                const number = paymentMethod.getAttribute('data-number');
                const instruction = paymentMethod.getAttribute('data-instruction');
                const type = paymentMethod.getAttribute('data-type');

                agentNumber.textContent = number + (type ? ' (' + type + ')' : '');
                paymentInstruction.textContent = instruction;

            } else {
                paymentDetails.classList.add('hidden');
            }
        }

        function placeOrder() {
            const name = document.getElementById('checkoutName')?.value || '';
            const phone = document.getElementById('checkoutPhone')?.value || '';
            const address = document.getElementById('checkoutAddress')?.value || '';
            const shipping = document.querySelector('input[name="shipping"]:checked');
            const payment = document.querySelector('input[name="payment"]:checked');

            if (!name || !phone || !address || !shipping || !payment) {
                alert('অনুগ্রহ করে সব তথ্য পূরণ করুন');
                return;
            }

            // Validate payment details if online payment
            if (payment.value !== 'cod') {
                const fromNumber = document.getElementById('fromNumber').value;
                const transactionId = document.getElementById('transactionId').value;
                const screenshot = document.getElementById('screenshot').files[0];

                if (!fromNumber || !transactionId || !screenshot) {
                    alert('অনুগ্রহ করে পেমেন্টের সব তথ্য পূরণ করুন');
                    return;
                }
            }

            // Disable button to prevent double submission
            const submitButton = document.querySelector('button[onclick="placeOrder()"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = 'অর্ডার করা হচ্ছে...';
            }

            // Prepare form data
            const formData = new FormData();
            formData.append('name', name);
            formData.append('phone', phone);
            formData.append('address', address);
            formData.append('delivery_charge_id', shipping.value);
            formData.append('payment_method', payment.value);

            // Add payment details if not COD
            if (payment.value !== 'cod') {
                formData.append('fromNumber', document.getElementById('fromNumber').value);
                formData.append('transactionId', document.getElementById('transactionId').value);
                formData.append('screenshot', document.getElementById('screenshot').files[0]);
            }

            // Add CSRF token
            formData.append('_token', '{{ csrf_token() }}');

            // Submit to backend
            fetch('{{ route('front.honey.checkout') }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Delete incomplete order on successful order placement
                        // This is handled on the backend, but we can also call it here as a backup
                        const deleteFormData = new FormData();
                        deleteFormData.append('_token', '{{ csrf_token() }}');
                        fetch('{{ route('front.honey.incomplete-order') }}', {
                            method: 'POST',
                            body: deleteFormData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        }).catch(() => {
                            // Ignore errors - backend should handle deletion
                        });

                        showToast('success', 'সফল!', data.msg || 'অর্ডার সফলভাবে সম্পন্ন হয়েছে!');
                        setTimeout(() => {
                            window.location.href = data.url;
                        }, 2000);
                    } else {
                        showToast('error', 'ত্রুটি!', data.msg ||
                            'অর্ডার সম্পন্ন করতে সমস্যা হয়েছে। অনুগ্রহ করে আবার চেষ্টা করুন।');
                        if (submitButton) {
                            submitButton.disabled = false;
                            submitButton.textContent = 'অর্ডার করুন';
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('error', 'ত্রুটি!', 'একটি ত্রুটি হয়েছে। অনুগ্রহ করে আবার চেষ্টা করুন।');
                    if (submitButton) {
                        submitButton.disabled = false;
                        submitButton.textContent = 'অর্ডার করুন';
                    }
                });
        }

        // Toast Notification Function
        function showToast(type, title, message) {
            // Remove existing toast if any
            const existingToast = document.querySelector('.toast-notification');
            if (existingToast) {
                existingToast.remove();
            }

            const toast = document.createElement('div');
            toast.className = `toast-notification toast-${type}`;

            const icon = type === 'success' ? '✓' : '✕';
            const iconBg = type === 'success' ? 'rgba(255, 255, 255, 0.3)' : 'rgba(255, 255, 255, 0.3)';

            toast.innerHTML = `
                <div class="toast-icon" style="background: ${iconBg};">${icon}</div>
                <div class="toast-content">
                    <div class="toast-title">${title}</div>
                    <div class="toast-message">${message}</div>
                </div>
                <button class="toast-close" onclick="this.parentElement.remove()">×</button>
            `;

            document.body.appendChild(toast);

            // Auto remove after 5 seconds
            setTimeout(() => {
                toast.classList.add('hiding');
                setTimeout(() => {
                    if (toast.parentElement) {
                        toast.remove();
                    }
                }, 300);
            }, 5000);
        }
    </script>

    <footer class="text-center py-8 bg-yellow-100 text-lg">© 2026 MiniBee Honey. Developed by <a
            href="https://nixsoftware.net" target="_blank" class="text-blue-500 hover:text-blue-700">Nix Software</a>
    </footer>

</body>

</html>
