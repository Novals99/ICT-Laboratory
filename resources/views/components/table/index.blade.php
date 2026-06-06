<div {{ $attributes->merge(['class' => 'panel-table-card']) }}>
    <div class="panel-table-wrap">
        <table class="panel-table">
            {{ $slot }}
        </table>
    </div>
</div>
{{-- pecahkan smua komponen ges buahahayy
- ngurusin gimana wrapper table ini ngatur rounded, border, bg, scroll horizontal, sama darkmode --}}