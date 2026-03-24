<div id="wufoo-{{ $formId }}"></div>
<script type="text/javascript">
var {{ $formId }};
(function(d, t) {
    var s = d.createElement(t), options = {
        'userName':'{{ config('services.wufoo.username', 'yourwufooaccount') }}',
        'formHash':'{{ $formId }}',
        'autoResize':true,
        'height':'577',
        'async':true,
        @if(!$showHeader)
        'header':'hide',
        @endif
        'ssl':true
    };
    s.src = ('https:' == d.location.protocol ? 'https://' : 'http://') + 'secure.wufoo.com/scripts/embed/form.js';
    s.onload = s.onreadystatechange = function() {
        var rs = this.readyState;
        if (rs) if (rs != 'complete') if (rs != 'loaded') return;
        try {
            {{ $formId }} = new WufooForm();
            {{ $formId }}.initialize(options);
            {{ $formId }}.display();
        } catch (e) {}
    };
    var scr = d.getElementsByTagName(t)[0], par = scr.parentNode;
    par.insertBefore(s, scr);
})(document, 'script');
</script>
