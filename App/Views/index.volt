
<div class="ui message ">
Если модуль включен, то на АТС становится доступна учетная запись SIP "<b>apv_askozia</b>".
<br>Для автоматической настройки телефона необходимо сбросить его к заводским настройками.
<br>Если телефон подключается к АТС впервые, то он будет зарегистрирован на учетной записи "<b>apv_askozia</b>".
<br>Для настройки телефона необходимо с него позвонить на номер "<b>{{ form.getValue('extension') }}</b>", где XXX - это внутренний номер на АТС.
<br><br>
Автонастройка возможна только для локальной сети предприятия, для телефонов <b>Yealink, Snom, Fanvil</b>.
</div>

<form class="ui large grey segment form" id="module-autoprovision-form">

    <div class="eight wide field">
        <label>{{ t._('mod_Autoprovision_Extension') }}</label>
        {{ form.render('extension') }}
    </div>

    <div class="eight wide field">
        <label>{{ t._('mod_Autoprovision_pbx_host') }}</label>
        {{ form.render('pbx_host') }}
    </div>

    <div class="eight wide field">
        <div class="ui equal width grid">
            <div class="column">
                <label>{{ t._('mod_Autoprovision_mac_white') }}</label>
                {{ form.render('mac_white') }}
            </div>
            <div class="column">
                <label>{{ t._('mod_Autoprovision_mac_black') }}</label>
                {{ form.render('mac_black') }}
            </div>
        </div>
    </div>

    <div class="eight wide field">
        <label>{{ t._('mod_Autoprovision_additional_params') }}</label>
        {{ form.render('additional_params') }}
    </div>

    {{ partial("partials/submitbutton",['indexurl':'pbx-extension-modules/index/']) }}
</form>