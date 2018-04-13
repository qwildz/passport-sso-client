@if(session()->get('sso_session_state'))
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=UTF-8"/>
    <meta name="robots" content="noindex, nofollow">
    <title>RP Frame</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/3.1.9-1/core.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/3.1.9-1/sha256.min.js"></script>
    <script>
        if (self == top) {
            window.location.href = 'about:blank';
        }

        let client_id, rp_origin, timer_id, op_target_origin;

        client_id = '{{ config('sso.client_id') }}';
        rp_origin = '{{ config('sso.redirect') }}';
        op_target_origin = '{{ config('sso.url') }}';

        timer_id = null;

        let state = "unchanged";

        let mes = "{{ config('sso.client_id') . ' ' . session()->get('sso_session_state') }}";
        @if(config('app.debug'))
        console.log('mes = ' + mes);

        @endif

        function check_session() {
            let targetOrigin = op_target_origin;
            @if(config('app.debug'))
            console.log('session_state = {{ session()->get('sso_session_state') }}');
                    @endif
            let opFrame = window.parent.document.getElementById("opFrame");
            if (opFrame) {
                let win = opFrame.contentWindow;
                if (win) {
                    win.postMessage(mes, targetOrigin);
                    @if(config('app.debug'))
                    console.log('client_id : ' + client_id + ' origin : ' + rp_origin);
                    @endif
                }
            }
                    @if(config('app.debug'))
            else {
                console.log('no opFrame');
            }
            @endif
        }

        function setTimer() {
            check_session();
            clearTimer();
            timer_id = setInterval("check_session()", 1 * 1000);
        }

        function clearTimer() {
            if (timer_id) {
                window.clearInterval(timer_id);
                timer_id = null;
            }
        }

        window.addEventListener("message", receiveMessage, false);

        function receiveMessage(e) {
            let targetOrigin = op_target_origin;
            if (e.origin !== targetOrigin) {
                @if(config('app.debug'))
                console.log(e.origin + ' !== ' + targetOrigin);
                @endif
                    return;
            }
            state = e.data;
            @if(config('app.debug'))
            console.log('rpframe received ' + state);
            @endif
            if (state == 'changed') {
                clearTimer();
                logout();
            }
        }

        setTimer();

        function logout() {
            window.parent.location.href = '{{ config('sso.logout_frame') }}';
        }
    </script>
</head>
<body>
</body>
</html>
@endif