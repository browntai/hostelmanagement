<!-- ShimaHome AI Support Bot Widget -->
<div id="shimaChatbot">
    <button id="chatbot-toggle" title="AI Support" style="
        position:fixed; bottom:20px; right:20px; z-index:9999;
        width:56px; height:56px; border-radius:50%;
        background:linear-gradient(135deg,#17c788,#129d6b);
        border:none; color:white; font-size:22px;
        box-shadow:0 4px 15px rgba(95,118,232,.4);
        cursor:pointer; transition:transform .3s;
    " onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
        <i class="fas fa-robot"></i>
    </button>

    <div id="chatbot-panel" style="
        display:none; position:fixed; bottom:85px; right:20px; z-index:9999;
        width:360px; max-height:500px; border-radius:16px;
        background:white; box-shadow:0 10px 40px rgba(0,0,0,.15);
        overflow:hidden; font-family:inherit;
    ">
        <div style="background:linear-gradient(135deg,#17c788,#129d6b); color:white; padding:16px 20px;">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="mb-0 text-white font-weight-bold"><i class="fas fa-robot mr-2"></i>ShimaHome AI</h6>
                    <small style="opacity:.8;">Ask me anything about your stay</small>
                </div>
                <button id="chatbot-close" style="background:none;border:none;color:white;font-size:18px;cursor:pointer;">&times;</button>
            </div>
        </div>

        <div id="chatbot-messages" style="
            height:320px; overflow-y:auto; padding:15px;
            background:#f8f9fa;
        ">
            <div class="chatbot-msg bot">
                <div style="background:white; border-radius:12px 12px 12px 0; padding:10px 14px;
                    margin-bottom:10px; box-shadow:0 1px 3px rgba(0,0,0,.08); max-width:85%; font-size:13px;">
                    👋 Hello! I'm the ShimaHome AI assistant. I can help with <strong>payments</strong>,
                    <strong>bookings</strong>, <strong>maintenance</strong>, <strong>account issues</strong>,
                    and <strong>tenant rights</strong>. What do you need help with?
                </div>
            </div>
        </div>

        <div style="padding:12px; border-top:1px solid #eee; background:white;">
            <div class="d-flex">
                <input type="text" id="chatbot-input" class="form-control border-0 bg-light" placeholder="Type your question…" style="border-radius:20px;font-size:13px;">
                <button id="chatbot-send" class="btn btn-primary btn-sm ml-2" style="border-radius:50%;width:36px;height:36px;padding:0;">
                    <i class="fas fa-paper-plane" style="font-size:12px;"></i>
                </button>
            </div>
            <div class="mt-2 d-flex flex-wrap" style="gap:4px;">
                <button class="btn btn-outline-primary btn-sm chatbot-quick" data-q="How do I pay rent?" style="border-radius:20px;font-size:11px;padding:2px 10px;">💳 Payments</button>
                <button class="btn btn-outline-primary btn-sm chatbot-quick" data-q="How do I report a repair?" style="border-radius:20px;font-size:11px;padding:2px 10px;">🔧 Repairs</button>
                <button class="btn btn-outline-primary btn-sm chatbot-quick" data-q="What are my tenant rights?" style="border-radius:20px;font-size:11px;padding:2px 10px;">⚖️ Rights</button>
                <button class="btn btn-outline-primary btn-sm chatbot-quick" data-q="How do I book a room?" style="border-radius:20px;font-size:11px;padding:2px 10px;">🏠 Booking</button>
            </div>
        </div>
    </div>
</div>

<script>
$(function(){
    var $panel   = $('#chatbot-panel'),
        $msgs    = $('#chatbot-messages'),
        $input   = $('#chatbot-input');

    $('#chatbot-toggle').click(function(){ $panel.slideToggle(200); $input.focus(); });
    $('#chatbot-close').click(function(){ $panel.slideUp(200); });

    function addMessage(text, who) {
        var align = who === 'user' ? 'text-right' : '';
        var bg    = who === 'user'
            ? 'background:linear-gradient(135deg,#17c788,#129d6b);color:white;border-radius:12px 12px 0 12px;'
            : 'background:white;border-radius:12px 12px 12px 0;';
        var ml    = who === 'user' ? 'margin-left:auto;' : '';
        $msgs.append(
            '<div class="chatbot-msg '+who+' '+align+'" style="margin-bottom:10px;">'+
            '<div style="'+bg+' padding:10px 14px;box-shadow:0 1px 3px rgba(0,0,0,.08);max-width:85%;font-size:13px;'+ml+'">'+
            text+'</div></div>'
        );
        $msgs.scrollTop($msgs[0].scrollHeight);
    }

    function sendQuestion(q) {
        if (!q.trim()) return;
        addMessage(q, 'user');
        $input.val('');

        // Typing indicator
        addMessage('<i class="fas fa-spinner fa-spin"></i> Thinking...', 'bot');

        $.ajax({
            url: 'chatbot-api.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ question: q }),
            success: function(res) {
                $msgs.find('.chatbot-msg.bot:last').remove(); // remove typing
                addMessage(res.answer, 'bot');
            },
            error: function() {
                $msgs.find('.chatbot-msg.bot:last').remove();
                addMessage('Sorry, I encountered an error. Please try again.', 'bot');
            }
        });
    }

    $('#chatbot-send').click(function(){ sendQuestion($input.val()); });
    $input.keypress(function(e){ if(e.which===13) sendQuestion($input.val()); });
    $('.chatbot-quick').click(function(){ sendQuestion($(this).data('q')); });
});
</script>
