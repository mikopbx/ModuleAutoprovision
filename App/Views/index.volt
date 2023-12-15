
<form class="ui large grey segment form" id="module-autoprovision-form">
    <div class="ui top attached tabular menu">
      <a class="active item" data-tab="phone-settings">{{ t._('mod_Autoprovision_phone_settings_title') }}</a>
      <a class="item" data-tab="templates">{{ t._('mod_Autoprovision_phone_templates') }}</a>
      <a class="item" data-tab="general-settings">{{ t._('mod_Autoprovision_general_settings') }}</a>
      <a class="item" data-tab="other-pbx">{{ t._('mod_Autoprovision_other_pbx') }}</a>
      <a class="item" data-tab="pnp">{{ t._('mod_Autoprovision_pnp') }}</a>
    </div>

    <div class="ui bottom attached tab segment" data-tab="other-pbx">
      <div class="ui message ">
          {{ t._('mod_Autoprovision_other_pbx_header') }}
      </div>
      <a id="add-new-other_pbx-button" class="ui blue button"><i class="add circle icon"></i>{{ t._('mod_Autoprovision_addNew') }}</a>
      <table id="other_pbx" class="ui celled table">
        <thead><tr>
          <th>{{ t._('mod_Autoprovision_other_pbx_name') }}</th>
          <th>{{ t._('mod_Autoprovision_other_pbx_address') }}</th>
          <th class="collapsing right aligned"></th>
        </tr></thead>
        <tbody>
            {% for pbx in otherPBX %}
            <tr id="{{ pbx['id'] }}"
              {% if loop.first %}
              style="display: none"
              {% endif %}
            >
                <td data-label="name" data-id="{{ pbx['id'] }}">
                    <div class="ui fluid mini icon input"><input type="text" name="other_pbx-name-{{ pbx['id'] }}" placeholder="" value="{{ pbx['name'] }}"></div>
                </td>
                <td data-label="address" data-id="{{ pbx['id'] }}">
                    <div class="ui fluid mini icon input"><input type="text" name="other_pbx-address-{{ pbx['id'] }}" placeholder="" value="{{ pbx['address'] }}"></div>
                </td>
                <td data-label="actions" class="right aligned">
                    <div class="ui compact basic icon buttons action-buttons">
                        <a href="#" onclick="moduleAutoprovision.removePbxRow('{{ pbx['id'] }}', this)"  class="ui button delete popuped two-steps-delete" data-content=""><i class="icon red trash"></i> 	</a>
                    </div>
                </td>
          </tr>
          {% endfor %}
        </tbody>
      </table>
    </div>

    <div class="ui bottom attached tab segment" data-tab="pnp">
        <div class="ui message ">
            {{ t._('mod_Autoprovision_header',['extension':form.getValue('extension')]) }}
        </div>
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
    </div>
    <div class="ui bottom attached tab segment" data-tab="general-settings">
        <div class="ui message"> {{ t._('mod_Autoprovision_templates_uri_header') }}</div>
        <a id="add-new-templates_uri-button" class="ui blue button"><i class="add circle icon"></i>{{ t._('mod_Autoprovision_addNew') }}</a>
        <table id="templates_uri" class="ui celled table">
            <thead><tr>
              <th class="">{{ t._('mod_Autoprovision_templates_uri_uri') }}</th>
              <th class="four wide">{{ t._('mod_Autoprovision_templates_uri_template') }}</th>
              <th class="collapsing right aligned"></th>
            </tr></thead>
            <tbody>
              {% for template in templatesUri %}
              <tr id="{{ template['id'] }}"
                {% if loop.first %}
                style="display: none"
                {% endif %}
              >
                <td data-label="uri" data-id="{{ template['id'] }}">
                    <div class="ui fluid mini icon input"><input type="text"  name="templates_uri-uri-{{ template['id'] }}" placeholder="" value="{{ template['uri'] }}"></div>
                </td>
                <td data-label="template" data-id="{{ template['id'] }}">
                    <div class="ui dropdown">
                      <input type="hidden" name="templates_uri-templateId-{{ template['id'] }}" value="{{ template['templateId'] }}">
                      <i class="file alternate icon"></i>
                      <span class="text">{{ t._('mod_Autoprovision_filter_posts') }}</span>
                      <div class="menu">
                        <div class="ui icon search input">
                          <i class="search icon"></i>
                          <input type="text" placeholder="{{ t._('mod_Autoprovision_search_tags') }}">
                        </div>
                        <div class="divider"></div>
                        <div class="scrolling menu">
                          {% for pattern in templates %}
                          <div class="item" data-value="{{ pattern['id'] }}">{{ pattern['name'] }}</div>
                          {% endfor %}
                        </div>
                      </div>
                    </div>
                </td>
                <td data-label="actions" class="right aligned">
                    <div class="ui compact basic icon buttons action-buttons">
                        <a href="#" onclick="moduleAutoprovision.removeUriTemplate('{{ template['id'] }}', this)"  class="ui button delete popuped two-steps-delete" data-content=""><i class="icon red trash"></i> 	</a>
                    </div>
                </td>
              </tr>
              {% endfor %}
            </tbody>
        </table>
    </div>
    <div class="ui bottom attached active tab segment" data-tab="phone-settings">
      <div class="ui message"> {{ t._('mod_Autoprovision_templates_users_header') }}</div>

      <a id="add-new-phone_settings-button" class="ui blue button"><i class="add circle icon"></i>{{ t._('mod_Autoprovision_addNew') }}</a>
      <table id="phone_settings" class="ui celled table">
        <thead><tr>
          <th class="">{{ t._('mod_Autoprovision_phone_settings_user') }}</th>
          <th class="four wide">{{ t._('mod_Autoprovision_phone_settings_mac') }}</th>
          <th class="four wide">{{ t._('mod_Autoprovision_templates_uri_template') }}</th>
          <th class="collapsing right aligned"></th>
        </tr></thead>
        <tbody>
          {% for template in templatesUsers %}
          <tr id="{{ template['id'] }}"
            {% if loop.first %}
            style="display: none"
            {% endif %}
          >
            <td data-label="user" data-id="{{ template['id'] }}">
                <div class="ui dropdown">
                  <input type="hidden" name="phone_settings-userId-{{ template['id'] }}" value="{{ template['userId'] }}">
                  <i class="user icon"></i>
                  <span class="text">{{ t._('mod_Autoprovision_filter_posts') }}</span>
                  <div class="menu">
                    <div class="ui icon search input">
                      <i class="search icon"></i>
                      <input type="text" placeholder="{{ t._('mod_Autoprovision_search_tags') }}">
                    </div>
                    <div class="divider"></div>
                    <div class="scrolling menu">
                      <div class="item" data-value="">{{ t._('mod_Autoprovision_filter_posts') }}</div>
                      {% for user in users %}
                      <div class="item" data-value="{{ user['userid'] }}">{{ user['number'] }} "{{ user['callerid'] }} "</div>
                      {% endfor %}
                    </div>
                  </div>
                </div>
            </td>
            <td data-label="mac" data-id="{{ template['id'] }}">
                <div class="ui fluid mini icon input"><input type="text"  name="phone_settings-mac-{{ template['id'] }}" placeholder="" value="{{ template['mac'] }}"></div>
            </td>
            <td data-label="template" data-id="{{ template['id'] }}">
                <div class="ui dropdown">
                  <input type="hidden" name="phone_settings-templateId-{{ template['id'] }}" value="{{ template['templateId'] }}">
                  <i class="file alternate icon"></i>
                  <span class="text">{{ t._('mod_Autoprovision_filter_posts') }}</span>
                  <div class="menu">
                    <div class="ui icon search input">
                      <i class="search icon"></i>
                      <input type="text" placeholder="{{ t._('mod_Autoprovision_search_tags') }}">
                    </div>
                    <div class="divider"></div>
                    <div class="scrolling menu">
                      {% for pattern in templates %}
                      <div class="item" data-value="{{ pattern['id'] }}">{{ pattern['name'] }}</div>
                      {% endfor %}
                    </div>
                  </div>
                </div>
            </td>
            <td data-label="actions" class="right aligned">
                <div class="ui compact basic icon buttons action-buttons">
                    <a href="#" onclick="moduleAutoprovision.removeUserTemplate('{{ template['id'] }}', this)"  class="ui button delete popuped two-steps-delete" data-content=""><i class="icon red trash"></i> 	</a>
                </div>
            </td>
          </tr>
          {% endfor %}
        </tbody>
      </table>
    </div>

    <div class="ui bottom attached tab segment" data-tab="templates">
      <div class="ui message"> {{ t._('mod_Autoprovision_templates_header') }}</div>

      <a id="add-new-template-button" class="ui blue button"><i class="add circle icon"></i>{{ t._('mod_Autoprovision_addNew') }}</a>
      <table id="templates" class="ui celled table">
        <thead><tr>
          <th>{{ t._('mod_Autoprovision_template_name') }}</th>
          <th class="four wide" style="display: none"></th>
          <th class="collapsing right aligned"></th>
        </tr></thead>
        <tbody>
            {% for template in templates %}
            <tr id="{{ template['id'] }}"
              {% if loop.first %}
              style="display: none"
              {% endif %}
            >
                <td data-label="name" data-id="{{ template['id'] }}">
                    <div class="ui fluid mini icon input"><input type="text" name="templates-name-{{ template['id'] }}" placeholder="" value="{{ template['name'] }}"></div>
                </td>
                <td data-label="template" data-id="{{ template['id'] }}" class="right aligned" style="display: none">
                    <textarea name="templates-template-{{ template['id'] }}" >{{ template['template'] }}</textarea>
                    <div class="ui modal segment" data-id="{{ template['id'] }}" data-id-table="templates">
                      <i class="close icon"></i>
                        <div class="ui form">
                          <div class="field">
                            <label>{{ t._('mod_Autoprovision_edit_template') }}</label>
                            <textarea>{{ template['template'] }}</textarea>
                          </div>
                        </div>
                      <div class="actions">
                        <div class="ui positive right labeled icon button">
                          {{ t._('mod_Autoprovision_end_edit_template') }}
                          <i class="checkmark icon"></i>
                        </div>
                      </div>
                    </div>
                </td>
                <td data-label="actions" class="right aligned">
                    <div class="ui compact basic icon buttons action-buttons">
                        <a href="#" onclick="moduleAutoprovision.showTemplateOptions('{{ template['id'] }}', this)"  class="ui button delete popuped two-steps-delete" data-content=""><i class="icon cog"></i> 	</a>
                        <a href="#" onclick="moduleAutoprovision.removeTemplate('{{ template['id'] }}', this)"  class="ui button delete popuped two-steps-delete" data-content=""><i class="icon red trash"></i> 	</a>
                    </div>
                </td>
          </tr>
          {% endfor %}

        </tbody>
      </table>
    </div>

    {{ partial("partials/submitbutton",['indexurl':'pbx-extension-modules/index/']) }}
</form>