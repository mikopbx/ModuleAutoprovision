
<div class="ui message ">
    {{ t._('mod_Autoprovision_header',['extension':form.getValue('extension')]) }}
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