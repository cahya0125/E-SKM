@php
    $flashAlerts = collect([
        ['type' => 'success', 'message' => session('success')],
        ['type' => 'error', 'message' => session('error')],
        ['type' => 'warning', 'message' => session('warning')],
        ['type' => 'info', 'message' => session('info')],
    ])->filter(fn (array $alert) => filled($alert['message']))->values();
@endphp

<div x-data="alertCenter(@js($flashAlerts))" x-init="init()" x-on:app-alert.window="show($event.detail)" x-on:app-confirm.window="confirm($event.detail)" x-cloak class="pointer-events-none fixed inset-0 z-50" aria-live="polite">
    <div class="pointer-events-none absolute inset-x-4 top-20 flex flex-col items-end gap-3 sm:inset-x-auto sm:right-6 sm:w-[min(24rem,calc(100vw-3rem))]">
    <template x-for="alert in alerts" :key="alert.id">
        <div x-show="alert.visible" x-transition:enter="transition duration-300 ease-out" x-transition:enter-start="translate-y-2 opacity-0 sm:translate-x-4 sm:translate-y-0" x-transition:enter-end="translate-x-0 translate-y-0 opacity-100" x-transition:leave="transition duration-200 ease-in" x-transition:leave-start="translate-x-0 opacity-100" x-transition:leave-end="translate-x-4 opacity-0" class="pointer-events-auto flex w-full items-start gap-3 rounded-xl border bg-white p-4 shadow-lg" :class="styles[alert.type].border" role="alert">
            <span class="grid h-9 w-9 shrink-0 place-content-center rounded-lg" :class="styles[alert.type].iconBackground"><i class="fa-solid text-sm" :class="styles[alert.type].icon" aria-hidden="true"></i></span>
            <div class="min-w-0 flex-1"><p class="text-sm font-semibold text-slate-800" x-text="alert.title || styles[alert.type].title"></p><p class="mt-1 wrap-break-word text-xs leading-5 text-slate-500" x-text="alert.message"></p></div>
            <button type="button" class="grid h-7 w-7 shrink-0 cursor-pointer place-content-center rounded-md text-slate-400 transition hover:bg-slate-100 hover:text-slate-700" title="Tutup notifikasi" aria-label="Tutup notifikasi" @click="dismiss(alert.id)"><i class="fa-solid fa-xmark text-xs" aria-hidden="true"></i></button>
        </div>
    </template>
    </div>

    <div x-show="confirmation" x-transition.opacity class="pointer-events-auto absolute inset-0 grid place-items-center bg-slate-950/45 p-4" role="presentation">
        <section x-show="confirmation" x-transition class="w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl" role="alertdialog" aria-modal="true" aria-labelledby="confirm-title" @click.outside="resolveConfirmation(false)" @keydown.escape.window="resolveConfirmation(false)">
            <div class="flex items-start gap-3"><span class="grid h-10 w-10 shrink-0 place-content-center rounded-xl bg-red-100 text-red-600"><i class="fa-solid fa-trash-can text-sm" aria-hidden="true"></i></span><div><h2 id="confirm-title" class="text-base font-bold text-slate-800" x-text="confirmation?.title"></h2><p class="mt-1 text-sm leading-6 text-slate-500" x-text="confirmation?.message"></p></div></div>
            <div class="mt-6 flex justify-end gap-3"><button type="button" class="cursor-pointer rounded-lg border border-slate-200 px-4 py-2.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-50" @click="resolveConfirmation(false)" x-text="confirmation?.cancelText"></button><button type="button" class="cursor-pointer rounded-lg bg-[#c43b2d] px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-[#a83227]" @click="resolveConfirmation(true)" x-text="confirmation?.confirmText"></button></div>
        </section>
    </div>
</div>