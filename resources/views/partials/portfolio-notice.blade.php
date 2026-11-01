{{-- First-visit notice: this site is a portfolio project. Once dismissed, it stays closed in this browser. --}}
@if (config('wedding.demo.enabled'))
    <dialog id="portfolio-notice" aria-labelledby="portfolio-notice-title"
        class="m-auto w-[calc(100%-2rem)] max-w-md rounded-2xl bg-[#FDFAF6] p-6 text-[#1A1A1A] shadow-xl backdrop:bg-black/60 sm:p-8">
        <h2 id="portfolio-notice-title" class="font-serif text-2xl font-bold">{{ __('Portfolio project') }}</h2>
        <p class="mt-3 text-sm leading-relaxed text-[#6B665E]">{{ __('This website is a portfolio project, built to demonstrate experience in building a Laravel (PHP) web application. It is not a real business: providers are fictional, and no real bookings or payments take place.') }}</p>
        <form method="dialog" class="mt-6">
            <button type="submit" autofocus class="w-full rounded-lg bg-[#1A1A1A] px-4 py-3 text-sm font-semibold text-white hover:bg-[#C8956C]">{{ __('Got it') }}</button>
        </form>
    </dialog>
    <script>
        (function () {
            var notice = document.getElementById('portfolio-notice');
            try {
                if (localStorage.getItem('portfolio_notice_seen')) return;
            } catch (e) {}
            notice.addEventListener('close', function () {
                try {
                    localStorage.setItem('portfolio_notice_seen', '1');
                } catch (e) {}
            });
            notice.showModal();
        })();
    </script>
@endif
