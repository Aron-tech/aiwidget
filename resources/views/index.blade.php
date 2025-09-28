<!DOCTYPE html>
<html lang="{{App::getLocale()}}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@lang('pages.title')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'ai-blue': '#00D4FF',
                        'ai-purple': '#8B5CF6',
                        'ai-pink': '#EC4899',
                        'ai-orange': '#F97316',
                        'dark-bg': '#0F0B1F'
                    },
                    backgroundImage: {
                        'gradient-radial': 'radial-gradient(var(--tw-gradient-stops))',
                        'ai-gradient': 'linear-gradient(135deg, #00D4FF 0%, #8B5CF6 50%, #EC4899 100%)',
                        'dark-gradient': 'linear-gradient(135deg, #0F0B1F 0%, #1E1B4B 50%, #312E81 100%)'
                    }
                }
            }
        }
    </script>
    <style>
        .floating-animation {
            animation: floating 6s ease-in-out infinite;
        }

        @keyframes floating {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        .glow-effect {
            box-shadow: 0 0 40px rgba(139, 92, 246, 0.3);
        }

        .network-bg {
            background-image:
                radial-gradient(circle at 20% 30%, rgba(0, 212, 255, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 70%, rgba(139, 92, 246, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 90%, rgba(236, 72, 153, 0.1) 0%, transparent 50%);
        }

        .pricing-card:hover {
            transform: translateY(-10px);
            transition: all 0.3s ease;
        }

        .language-selector {
            position: relative;
            display: inline-block;
        }

        .language-button {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            padding: 8px 12px;
            color: white;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .language-button:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.3);
        }

        .language-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background: rgba(15, 11, 31, 0.95);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            min-width: 140px;
            padding: 8px 0;
            margin-top: 8px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            z-index: 100;
        }

        .language-dropdown.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .language-option {
            padding: 10px 16px;
            color: #D1D5DB;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .language-option:hover {
            background: rgba(139, 92, 246, 0.2);
            color: white;
        }

        .language-option.active {
            background: linear-gradient(135deg, #00D4FF 0%, #8B5CF6 50%, #EC4899 100%);
            color: white;
        }

        .flag-icon {
            width: 20px;
            height: 15px;
            border-radius: 2px;
            display: inline-block;
        }
    </style>
</head>
<body class="bg-dark-bg text-white overflow-x-hidden">
<nav class="fixed w-full z-50 bg-dark-bg/90 backdrop-blur-lg border-b border-white/10">
    <div class="max-w-7xl mx-auto px-6 py-4">
        <div class="flex justify-between items-center">
            <div class="text-2xl font-bold bg-ai-gradient bg-clip-text text-transparent">
                ConversiveAI
            </div>
            <div class="hidden md:flex space-x-8">
                <a href="#features" class="hover:text-ai-blue transition-colors">@lang('pages.nav.features')</a>
                <a href="#pricing" class="hover:text-ai-purple transition-colors">@lang('pages.nav.pricing')</a>
                <a id="demo-open" href="#demo" class="hover:text-ai-pink transition-colors">@lang('pages.nav.demo')</a>
                <a href="#contact" class="hover:text-ai-orange transition-colors">@lang('pages.nav.contact')</a>
            </div>
            <div class="flex items-center gap-4">
                @livewire('localeswitcher', ['reload_page' => true])
                <a href="{{route('login')}}" class="bg-ai-gradient text-white px-6 py-2 rounded-full font-semibold hover:opacity-90 transition-opacity">
                    @lang('pages.nav.get_started')
                </a>
            </div>
        </div>
    </div>
</nav>

<section class="relative min-h-screen flex items-center justify-center network-bg overflow-hidden">
    <div class="absolute inset-0">
        <div class="absolute top-20 left-20 w-2 h-2 bg-ai-blue rounded-full floating-animation"></div>
        <div class="absolute top-40 right-32 w-3 h-3 bg-ai-purple rounded-full floating-animation" style="animation-delay: -2s;"></div>
        <div class="absolute bottom-40 left-1/4 w-2 h-2 bg-ai-pink rounded-full floating-animation" style="animation-delay: -4s;"></div>
        <div class="absolute top-1/3 right-20 w-1 h-1 bg-ai-orange rounded-full floating-animation" style="animation-delay: -1s;"></div>
    </div>

    <div class="relative z-10 text-center max-w-6xl mx-auto px-6">
        <div class="mb-12 floating-animation">
            <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgdmlld0JveD0iMCAwIDIwMCAyMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxjaXJjbGUgY3g9IjEwMCIgY3k9IjEwMCIgcj0iODAiIGZpbGw9InVybCgjZ3JhZGllbnQpIiBmaWxsLW9wYWNpdHk9IjAuMiIvPgo8ZGVmcz4KPGxpbmVhckdyYWRpZW50IGlkPSJncmFkaWVudCIgeDE9IjAiIHkxPSIwIiB4Mj0iMSIgeTI9IjEiPgo8c3RvcCBzdG9wLWNvbG9yPSIjMDBENEZGIi8+CjxzdG9wIG9mZnNldD0iMC41IiBzdG9wLWNvbG9yPSIjOEI1Q0Y2Ii8+CjxzdG9wIG9mZnNldD0iMSIgc3RvcC1jb2xvcj0iI0VDNDg5OSIvPgo8L2xpbmVhckdyYWRpZW50Pgo8L2RlZnM+Cjwvc3ZnPgo="
                 alt="ConversiveAI Network"
                 class="w-32 h-32 mx-auto mb-8 glow-effect rounded-full">
        </div>

        <h1 class="text-6xl md:text-8xl font-bold mb-6">
            <span class="bg-ai-gradient bg-clip-text text-transparent">@lang('pages.hero.title')</span>
        </h1>

        <p class="text-xl md:text-2xl text-gray-300 mb-8 max-w-3xl mx-auto leading-relaxed">
            @lang('pages.hero.subtitle')
        </p>

        <div class="flex flex-col md:flex-row gap-4 justify-center items-center mb-12">
            <a href="#demo" class="border border-white/30 text-white px-8 py-4 rounded-full text-lg font-semibold hover:bg-white/10 transition-all">
                @lang('pages.hero.demo_button')
            </a>
        </div>
    </div>

    <div class="absolute inset-0 opacity-20">
        <svg class="w-full h-full" viewBox="0 0 1200 800">
            <defs>
                <linearGradient id="line-gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" style="stop-color:#00D4FF;stop-opacity:1" />
                    <stop offset="50%" style="stop-color:#8B5CF6;stop-opacity:1" />
                    <stop offset="100%" style="stop-color:#EC4899;stop-opacity:1" />
                </linearGradient>
            </defs>
            <path d="M100,200 Q600,100 1100,300" stroke="url(#line-gradient)" stroke-width="2" fill="none" opacity="0.6"/>
            <path d="M200,600 Q600,500 1000,200" stroke="url(#line-gradient)" stroke-width="1" fill="none" opacity="0.4"/>
            <path d="M50,400 Q300,600 800,400" stroke="url(#line-gradient)" stroke-width="1" fill="none" opacity="0.3"/>
        </svg>
    </div>
</section>

<section id="features" class="py-20 bg-gradient-to-b from-dark-bg to-slate-900">
    <div class="max-w-7xl mx-auto px-6">
        <div class="text-center mb-16">
            <h2 class="text-4xl md:text-6xl font-bold mb-6 bg-ai-gradient bg-clip-text text-transparent">
                @lang('pages.features.title')
            </h2>
            <p class="text-xl text-gray-300 max-w-3xl mx-auto">
                @lang('pages.features.subtitle')
            </p>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
            <div class="bg-white/5 backdrop-blur-lg rounded-2xl p-8 hover:bg-white/10 transition-all duration-300 border border-white/10">
                <div class="w-16 h-16 bg-ai-blue/20 rounded-full flex items-center justify-center mb-6">
                    <svg class="w-8 h-8 text-ai-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold mb-4 text-white">@lang('pages.features.quick_integration.title')</h3>
                <p class="text-gray-300">@lang('pages.features.quick_integration.description')</p>
            </div>

            <div class="bg-white/5 backdrop-blur-lg rounded-2xl p-8 hover:bg-white/10 transition-all duration-300 border border-white/10">
                <div class="w-16 h-16 bg-ai-purple/20 rounded-full flex items-center justify-center mb-6">
                    <svg class="w-8 h-8 text-ai-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold mb-4 text-white">@lang('pages.features.intelligent_ai.title')</h3>
                <p class="text-gray-300">@lang('pages.features.intelligent_ai.description')</p>
            </div>

            <div class="bg-white/5 backdrop-blur-lg rounded-2xl p-8 hover:bg-white/10 transition-all duration-300 border border-white/10">
                <div class="w-16 h-16 bg-ai-pink/20 rounded-full flex items-center justify-center mb-6">
                    <svg class="w-8 h-8 text-ai-pink" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold mb-4 text-white">@lang('pages.features.customizable.title')</h3>
                <p class="text-gray-300">@lang('pages.features.customizable.description')</p>
            </div>
        </div>
    </div>
</section>

<section id="pricing" class="py-20 bg-gradient-to-b from-slate-900 to-dark-bg">
    <div class="max-w-7xl mx-auto px-6">
        <div class="text-center mb-16">
            <h2 class="text-4xl md:text-6xl font-bold mb-6 bg-ai-gradient bg-clip-text text-transparent">
                @lang('pages.pricing.title')
            </h2>
            <p class="text-xl text-gray-300 max-w-3xl mx-auto">
                @lang('pages.pricing.subtitle')
            </p>
        </div>

        <div class="grid md:grid-cols-2 gap-8 max-w-5xl mx-auto">
            <div class="pricing-card bg-gradient-to-br from-white/10 to-white/5 backdrop-blur-lg rounded-3xl p-8 border border-white/20 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-ai-purple/20 rounded-full -translate-y-16 translate-x-16"></div>
                <div class="relative z-10">
                    <div class="flex items-center mb-6">
                        <div class="w-12 h-12 bg-ai-purple/20 rounded-full flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-ai-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-white">@lang('pages.pricing.system_fee.title')</h3>
                    </div>

                    <div class="space-y-4 mb-8">
                        <div class="bg-white/5 rounded-xl p-4">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-300">@lang('pages.pricing.system_fee.monthly')</span>
                                <span class="text-ai-blue font-bold">€4.99</span>
                            </div>
                        </div>
                        <div class="bg-white/5 rounded-xl p-4">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-300">@lang('pages.pricing.system_fee.quarterly')</span>
                                <span class="text-ai-purple font-bold">€13.99 <span class="text-xs text-green-400">(-6,6%)</span></span>
                            </div>
                        </div>
                        <div class="bg-white/5 rounded-xl p-4">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-300">@lang('pages.pricing.system_fee.semiannual')</span>
                                <span class="text-ai-pink font-bold">€24.99 <span class="text-xs text-green-400">(-16,5%)</span></span>
                            </div>
                        </div>
                        <div class="bg-white/5 rounded-xl p-4">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-300">@lang('pages.pricing.system_fee.annual')</span>
                                <span class="text-ai-orange font-bold">€45.99 <span class="text-xs text-green-400">(-23,2%)</span></span>
                            </div>
                        </div>
                        <div class="bg-gradient-to-r from-ai-purple/20 to-ai-pink/20 rounded-xl p-4 border border-ai-purple/30">
                            <div class="flex justify-between items-center">
                                <span class="text-white font-semibold">@lang('pages.pricing.system_fee.triennial')</span>
                                <span class="text-white font-bold text-lg">€129.99 <span class="text-xs text-green-400">(-27,7%)</span></span>
                            </div>
                            <div class="text-xs text-gray-300 mt-1">@lang('pages.pricing.system_fee.best_offer')</div>
                        </div>
                    </div>

                    <ul class="space-y-4 mb-8">
                        <li class="flex items-center text-gray-300">
                            <svg class="w-5 h-5 text-ai-purple mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            1 @lang('pages.pricing.features.website_integration')
                        </li>
                        <li class="flex items-center text-gray-300">
                            <svg class="w-5 h-5 text-ai-purple mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            @lang('pages.pricing.features.support')
                        </li>
                        <li class="flex items-center text-gray-300">
                            <svg class="w-5 h-5 text-ai-purple mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            @lang('pages.pricing.features.customization')
                        </li>
                        <li class="flex items-center text-gray-300">
                            <svg class="w-5 h-5 text-ai-purple mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            @lang('pages.pricing.features.analytics')
                        </li>
                    </ul>
                </div>
            </div>

            <div class="pricing-card bg-gradient-to-br from-white/10 to-white/5 backdrop-blur-lg rounded-3xl p-8 border border-white/20 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-ai-orange/20 rounded-full -translate-y-16 translate-x-16"></div>
                <div class="relative z-10">
                    <div class="flex items-center mb-6">
                        <div class="w-12 h-12 bg-ai-orange/20 rounded-full flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-ai-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-white">@lang('pages.pricing.usage_fee.title')</h3>
                    </div>

                    <div class="mb-8">
                        <div class="text-5xl font-bold text-white mb-2">
                            <span class="bg-ai-gradient bg-clip-text text-transparent">€0.002</span>
                        </div>
                        <p class="text-gray-300">@lang('pages.pricing.usage_fee.per_token')</p>
                    </div>

                    <div class="bg-white/5 rounded-2xl p-6 mb-8">
                        <h4 class="text-lg font-semibold text-white mb-4">@lang('pages.pricing.usage_fee.estimation')</h4>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between text-gray-300">
                                <span>@lang('pages.pricing.usage_fee.short_qa')</span>
                                <span class="text-ai-blue">~50-100 token</span>
                            </div>
                            <div class="flex justify-between text-gray-300">
                                <span>@lang('pages.pricing.usage_fee.medium_chat')</span>
                                <span class="text-ai-purple">~200-500 token</span>
                            </div>
                            <div class="flex justify-between text-gray-300">
                                <span>@lang('pages.pricing.usage_fee.detailed_help')</span>
                                <span class="text-ai-pink">~500-1000 token</span>
                            </div>
                        </div>
                    </div>

                    <div class="text-center bg-ai-gradient/10 rounded-xl p-4">
                        <p class="text-sm text-gray-300">
                            <span class="text-ai-blue font-semibold">1000 @lang('pages.pricing.usage_fee.cost_example')</span>
                            <span class="text-ai-orange font-semibold">€1-3</span> @lang('pages.pricing.usage_fee.title')
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-12">
            <p class="text-gray-400 mb-6">
                @lang('pages.pricing.no_fees')
            </p>
            <a href="{{route('login')}}" class="bg-ai-gradient text-white px-12 py-4 rounded-full text-xl font-semibold hover:opacity-90 transition-all transform hover:scale-105">
                @lang('pages.pricing.start_now')
            </a>
        </div>
    </div>
</section>

<section id="demo" class="py-20 bg-dark-bg">
    <div class="max-w-7xl mx-auto px-6 text-center">
        <h2 class="text-4xl md:text-6xl font-bold mb-8 bg-ai-gradient bg-clip-text text-transparent">
            @lang('pages.demo.title')
        </h2>
        <p class="text-xl text-gray-300 mb-12">
            @lang('pages.demo.subtitle')
        </p>

        <div class="bg-white/5 backdrop-blur-lg rounded-2xl p-8 max-w-2xl mx-auto border border-white/10">
            <p class="text-gray-400">
                @lang('pages.demo.features')
            </p>
        </div>
    </div>
</section>

<footer class="bg-gradient-to-t from-black to-dark-bg py-12 border-t border-white/10">
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex flex-col md:flex-row justify-between items-center">
            <div class="mb-6 md:mb-0">
                <div class="text-3xl font-bold bg-ai-gradient bg-clip-text text-transparent mb-2">
                    ConversiveAI
                </div>
                <p class="text-gray-400">@lang('pages.footer.tagline')</p>
            </div>

            <div class="flex space-x-8">
                <a href="#" class="text-gray-400 hover:text-white transition-colors">@lang('pages.footer.privacy')</a>
                <a href="#" class="text-gray-400 hover:text-white transition-colors">@lang('pages.footer.terms')</a>
            </div>
        </div>

        <div class="border-t border-white/10 mt-8 pt-8 text-center">
            <p class="text-gray-400">
                @lang('pages.footer.copyright')
            </p>
        </div>
    </div>
</footer>
<div id="conversiveai-widget-container"></div>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const btn = document.getElementById('demo-open');

        btn.addEventListener('click', () => {
            const target = document.querySelector('#conversiveai-widget-container');
            if (target) {
                target.click();
            }
        });
    });


    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    window.addEventListener('scroll', () => {
        const nav = document.querySelector('nav');
        if (window.scrollY > 100) {
            nav.classList.add('bg-dark-bg/95');
        } else {
            nav.classList.remove('bg-dark-bg/95');
        }
    });

    window.widgetConfig = {
        siteId: '17SAvu5CFo4sPhFIWp3D2ngT',
        widgetName: 'Demo',
    };
</script>
<script>
    window.widgetConfig = {
        siteId: '17SAvu5CFo4sPhFIWp3D2ngT',
        widgetName: 'Demo',
    };
</script>
<script src="https://szakdolgozat.test/js/widget.js"></script>
</body>
</html>
