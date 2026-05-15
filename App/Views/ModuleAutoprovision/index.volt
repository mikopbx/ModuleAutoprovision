
<form class="ui large grey segment form" id="module-autoprovision-form">
    <div class="ui top attached tabular menu">
      <a class="active item" data-tab="phone-settings">{{ t._('mod_Autoprovision_phone_settings_title') }}</a>
      <a class="item" data-tab="templates">{{ t._('mod_Autoprovision_phone_templates') }}</a>
      <a class="item" data-tab="general-settings">{{ t._('mod_Autoprovision_general_settings') }}</a>
      <a class="item" data-tab="other-pbx">{{ t._('mod_Autoprovision_other_pbx') }}</a>
      <a class="item" data-tab="pnp">{{ t._('mod_Autoprovision_pnp') }}</a>
      <a class="item" data-tab="firmware">{{ t._('mod_Autoprovision_firmware') }}</a>
    </div>

    <div class="ui bottom attached tab segment" data-tab="other-pbx">
      <div class="ui message ">
          {{ t._('mod_Autoprovision_other_pbx_header') }}
      </div>
      <a id="add-new-other_pbx-button" class="ui blue button"><i class="add circle icon"></i>{{ t._('mod_Autoprovision_addNew') }}</a>
      <table id="other_pbx" class="ui celled table" data-table-key="other_pbx" data-model="OtherPBX">
        <thead><tr>
          <th>{{ t._('mod_Autoprovision_other_pbx_name') }}</th>
          <th>{{ t._('mod_Autoprovision_other_pbx_address') }}</th>
          <th class="collapsing right aligned"></th>
        </tr></thead>
        <tbody>
            {% for pbx in otherPBX %}
            <tr id="{{ pbx['id']|e }}"
              {% if loop.first %}
              style="display: none"
              {% endif %}
            >
                <td data-label="name" data-id="{{ pbx['id']|e }}">
                    <div class="ui fluid mini icon input"><input type="text" name="other_pbx[{{ pbx['id']|e }}][name]" placeholder="" value="{{ pbx['name']|default('')|e }}"></div>
                </td>
                <td data-label="address" data-id="{{ pbx['id']|e }}">
                    <div class="ui fluid mini icon input"><input type="text" name="other_pbx[{{ pbx['id']|e }}][address]" placeholder="" value="{{ pbx['address']|default('')|e }}"></div>
                </td>
                <td data-label="actions" class="right aligned">
                    <div class="ui compact basic icon buttons action-buttons">
                        <a href="#" class="ui button delete popuped two-steps-delete remove-row" data-content=""><i class="icon red trash"></i> 	</a>
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
          <label>{{ t._('mod_Autoprovision_http_port') }}</label>
          {{ form.render('http_port') }}
          <div class="ui small note">{{ t._('mod_Autoprovision_http_port_hint') }}</div>
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

        <div class="eight wide field">
            <div class="ui toggle checkbox">
                {{ form.render('tftp_enabled') }}
                <label>{{ t._('mod_Autoprovision_tftp_enabled') }}</label>
            </div>
            <div class="ui small note">{{ t._('mod_Autoprovision_tftp_enabled_hint') }}</div>
        </div>
    </div>
    <div class="ui bottom attached tab segment" data-tab="general-settings">
        <div class="ui message"> {{ t._('mod_Autoprovision_templates_uri_header') }}</div>
        <a id="add-new-templates_uri-button" class="ui blue button"><i class="add circle icon"></i>{{ t._('mod_Autoprovision_addNew') }}</a>
        <table id="templates_uri" class="ui celled table" data-table-key="templates_uri" data-model="TemplatesUri">
            <thead><tr>
              <th class="">{{ t._('mod_Autoprovision_templates_uri_uri') }}</th>
              <th class="four wide">{{ t._('mod_Autoprovision_templates_uri_template') }}</th>
              <th class="collapsing right aligned"></th>
            </tr></thead>
            <tbody>
              {% for template in templatesUri %}
              <tr id="{{ template['id']|e }}"
                {% if loop.first %}
                style="display: none"
                {% endif %}
              >
                <td data-label="uri" data-id="{{ template['id']|e }}">
                    <div class="ui fluid mini icon input"><input type="text"  name="templates_uri[{{ template['id']|e }}][uri]" placeholder="" value="{{ template['uri']|default('')|e }}"></div>
                </td>
                <td data-label="template" data-id="{{ template['id']|e }}">
                    <div class="ui dropdown">
                      <input type="hidden" name="templates_uri[{{ template['id']|e }}][templateId]" value="{{ template['templateId']|default('')|e }}">
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
                          <div class="item" data-value="{{ pattern['id']|e }}">{{ pattern['name']|default('')|e }}</div>
                          {% endfor %}
                        </div>
                      </div>
                    </div>
                </td>
                <td data-label="actions" class="right aligned">
                    <div class="ui compact basic icon buttons action-buttons">
                        <a href="#" class="ui button delete popuped two-steps-delete remove-row" data-content=""><i class="icon red trash"></i> 	</a>
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
      <table id="phone_settings" class="ui celled table" data-table-key="phone_settings" data-model="TemplatesUsers">
        <thead><tr>
          <th class="">{{ t._('mod_Autoprovision_phone_settings_user') }}</th>
          <th class="four wide">{{ t._('mod_Autoprovision_phone_settings_mac') }}</th>
          <th class="four wide">{{ t._('mod_Autoprovision_templates_uri_template') }}</th>
          <th class="collapsing right aligned"></th>
        </tr></thead>
        <tbody>
          {% for template in templatesUsers %}
          <tr id="{{ template['id']|e }}"
            {% if loop.first %}
            style="display: none"
            {% endif %}
          >
            <td data-label="user" data-id="{{ template['id']|e }}">
                <div class="ui dropdown">
                  <input type="hidden" name="phone_settings[{{ template['id']|e }}][userId]" value="{{ template['userId']|default('')|e }}">
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
                      <div class="item" data-value="{{ user['userid']|e }}">{{ user['number']|default('')|e }} "{{ user['callerid']|default('')|e }} "</div>
                      {% endfor %}
                    </div>
                  </div>
                </div>
            </td>
            <td data-label="mac" data-id="{{ template['id']|e }}">
                <div class="ui fluid mini icon input"><input type="text"  name="phone_settings[{{ template['id']|e }}][mac]" placeholder="" value="{{ template['mac']|default('')|e }}"></div>
            </td>
            <td data-label="template" data-id="{{ template['id']|e }}">
                <div class="ui dropdown">
                  <input type="hidden" name="phone_settings[{{ template['id']|e }}][templateId]" value="{{ template['templateId']|default('')|e }}">
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
                      <div class="item" data-value="{{ pattern['id']|e }}">{{ pattern['name']|default('')|e }}</div>
                      {% endfor %}
                    </div>
                  </div>
                </div>
            </td>
            <td data-label="actions" class="right aligned">
                <div class="ui compact basic icon buttons action-buttons">
                    <a href="#" class="ui button delete popuped two-steps-delete remove-row" data-content=""><i class="icon red trash"></i> 	</a>
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
      <a id="load-example-templates-button" class="ui basic button"
         data-tooltip="{{ t._('mod_Autoprovision_load_examples_hint') }}"
         data-already-msg="{{ t._('mod_Autoprovision_load_examples_already_present') }}"
         data-failed-msg="{{ t._('mod_Autoprovision_load_examples_failed') }}"
         data-partial-msg="{{ t._('mod_Autoprovision_load_examples_partial') }}"
         data-unsaved-msg="{{ t._('mod_Autoprovision_load_examples_unsaved_warning') }}">
        <i class="cloud download icon"></i>{{ t._('mod_Autoprovision_load_examples') }}
      </a>
      <table id="templates" class="ui celled table" data-table-key="templates" data-model="Templates">
        <thead><tr>
          <th>{{ t._('mod_Autoprovision_template_name') }}</th>
          <th class="four wide" style="display: none"></th>
          <th class="collapsing right aligned"></th>
        </tr></thead>
        <tbody>
            {% for template in templates %}
            <tr id="{{ template['id']|e }}"
              {% if loop.first %}
              style="display: none"
              {% endif %}
            >
                <td data-label="name" data-id="{{ template['id']|e }}">
                    <div class="ui fluid mini icon input"><input type="text" name="templates[{{ template['id']|e }}][name]" placeholder="" value="{{ template['name']|default('')|e }}"></div>
                </td>
                <td data-label="template" data-id="{{ template['id']|e }}" class="right aligned" style="display: none">
                    <textarea name="templates[{{ template['id']|e }}][template]" >{{ template['template']|default('')|e }}</textarea>
                    <div class="ui modal segment" data-id="{{ template['id']|e }}" data-id-table="templates">
                      <i class="close icon"></i>
                        <div class="ui form">
                          <div class="field">
                            <label>{{ t._('mod_Autoprovision_edit_template') }}</label>
                            <textarea>{{ template['template']|default('')|e }}</textarea>
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
                        <a href="#" class="ui button popuped show-template-options" data-content=""><i class="icon cog"></i> 	</a>
                        <a href="#" class="ui button delete popuped two-steps-delete remove-row" data-content=""><i class="icon red trash"></i> 	</a>
                    </div>
                </td>
          </tr>
          {% endfor %}

        </tbody>
      </table>
    </div>

    <div class="ui bottom attached tab segment" data-tab="firmware">
        <div class="ui message">{{ t._('mod_Autoprovision_firmware_header') }}</div>

        <div id="firmware-dropzone" class="ui placeholder segment" style="cursor: pointer;">
            <div class="ui icon header">
                <i class="upload icon"></i>
                {{ t._('mod_Autoprovision_firmware_drop_hint') }}
            </div>
            <div class="ui primary button" id="firmware-browse">
                <i class="folder open icon"></i>
                {{ t._('mod_Autoprovision_firmware_browse') }}
            </div>
        </div>

        <div class="ui form" style="margin-top: 1em;">
            <div class="four fields">
                <div class="field">
                    <label>{{ t._('mod_Autoprovision_firmware_vendor') }}</label>
                    <select id="firmware-vendor" class="ui dropdown">
                        <option value="yealink">Yealink</option>
                        <option value="snom">Snom</option>
                        <option value="fanvil">Fanvil</option>
                        <option value="grandstream">Grandstream</option>
                        <option value="htek">Htek</option>
                    </select>
                </div>
                <div class="field">
                    <label>{{ t._('mod_Autoprovision_firmware_model') }}</label>
                    <input id="firmware-model" type="text" placeholder="T46S">
                </div>
                <div class="field">
                    <label>{{ t._('mod_Autoprovision_firmware_version') }}</label>
                    <input id="firmware-version" type="text" placeholder="66.86.0.15">
                </div>
                <div class="field">
                    <label>{{ t._('mod_Autoprovision_firmware_notes') }}</label>
                    <input id="firmware-notes" type="text">
                </div>
            </div>
        </div>

        <div id="firmware-progress" class="ui small indicating progress" data-percent="0" style="margin: 1em 0;">
            <div class="bar"><div class="progress"></div></div>
            <div class="label"></div>
        </div>

        <table id="firmware-table" class="ui celled table">
            <thead><tr>
                <th>{{ t._('mod_Autoprovision_firmware_vendor') }}</th>
                <th>{{ t._('mod_Autoprovision_firmware_model') }}</th>
                <th>{{ t._('mod_Autoprovision_firmware_filename') }}</th>
                <th>{{ t._('mod_Autoprovision_firmware_version') }}</th>
                <th>{{ t._('mod_Autoprovision_firmware_size') }}</th>
                <th>{{ t._('mod_Autoprovision_firmware_sha256') }}</th>
                <th class="collapsing right aligned"></th>
            </tr></thead>
            <tbody></tbody>
        </table>
        <div id="firmware-totals" class="ui small text"></div>

        <div id="firmware-edit-modal" class="ui modal">
            <i class="close icon"></i>
            <div class="header">{{ t._('mod_Autoprovision_firmware_edit_title') }}</div>
            <div class="content">
                <div class="ui form">
                    <input type="hidden" id="firmware-edit-id" value="">
                    <div class="two fields">
                        <div class="field">
                            <label>{{ t._('mod_Autoprovision_firmware_vendor') }}</label>
                            <select id="firmware-edit-vendor" class="ui dropdown">
                                <option value="yealink">Yealink</option>
                                <option value="snom">Snom</option>
                                <option value="fanvil">Fanvil</option>
                                <option value="grandstream">Grandstream</option>
                                <option value="htek">Htek</option>
                            </select>
                        </div>
                        <div class="field">
                            <label>{{ t._('mod_Autoprovision_firmware_model') }}</label>
                            <input id="firmware-edit-model" type="text">
                        </div>
                    </div>
                    <div class="two fields">
                        <div class="field">
                            <label>{{ t._('mod_Autoprovision_firmware_version') }}</label>
                            <input id="firmware-edit-version" type="text">
                        </div>
                        <div class="field">
                            <label>{{ t._('mod_Autoprovision_firmware_notes') }}</label>
                            <input id="firmware-edit-notes" type="text">
                        </div>
                    </div>
                    <div id="firmware-edit-error" class="ui red message" style="display: none;"></div>
                </div>
            </div>
            <div class="actions">
                <div class="ui cancel button">{{ t._('mod_Autoprovision_firmware_cancel') }}</div>
                <div id="firmware-edit-save" class="ui positive button">{{ t._('mod_Autoprovision_firmware_save') }}</div>
            </div>
        </div>
    </div>

    {{ partial("partials/submitbutton",['indexurl':'pbx-extension-modules/index/']) }}
</form>
