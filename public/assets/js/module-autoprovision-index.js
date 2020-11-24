"use strict";

/*
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Alexey Portnov, 12 2018
 */

/* global globalRootUrl,Config, Form */
var moduleAutoprovision = {
  $formObj: $('#module-autoprovision-form'),
  initialize: function () {
    function initialize() {
      // Динамическая проверка свободен ли внутренний номер
      moduleAutoprovision.initializeForm();
    }

    return initialize;
  }(),

  /**
      * Применение настроек модуля после изменения данных формы
      */
  applyConfigurationChanges: function () {
    function applyConfigurationChanges() {
      $.api({
        url: "".concat(Config.pbxUrl, "/pbxcore/api/modules/ModuleAutoprovision/reload"),
        on: 'now',
        successTest: function () {
          function successTest(response) {
            // test whether a JSON response is valid
            return Object.keys(response).length > 0 && response.result === true;
          }

          return successTest;
        }(),
        onSuccess: function () {
          function onSuccess() {// moduleAutoprovision.testConnection();
          }

          return onSuccess;
        }()
      });
    }

    return applyConfigurationChanges;
  }(),
  cbBeforeSendForm: function () {
    function cbBeforeSendForm(settings) {
      var result = settings;
      result.data = moduleAutoprovision.$formObj.form('get values');
      return result;
    }

    return cbBeforeSendForm;
  }(),
  cbAfterSendForm: function () {
    function cbAfterSendForm() {
      moduleAutoprovision.applyConfigurationChanges();
    }

    return cbAfterSendForm;
  }(),
  initializeForm: function () {
    function initializeForm() {
      Form.$formObj = moduleAutoprovision.$formObj;
      Form.url = "".concat(globalRootUrl, "module-autoprovision/save"); // Form.validateRules = moduleAutoprovision.validateRules;

      Form.cbBeforeSendForm = moduleAutoprovision.cbBeforeSendForm;
      Form.cbAfterSendForm = moduleAutoprovision.cbAfterSendForm;
      Form.initialize();
    }

    return initializeForm;
  }()
};
$(document).ready(function () {
  moduleAutoprovision.initialize();
});
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbInNyYy9tb2R1bGUtYXV0b3Byb3Zpc2lvbi1pbmRleC5qcyJdLCJuYW1lcyI6WyJtb2R1bGVBdXRvcHJvdmlzaW9uIiwiJGZvcm1PYmoiLCIkIiwiaW5pdGlhbGl6ZSIsImluaXRpYWxpemVGb3JtIiwiYXBwbHlDb25maWd1cmF0aW9uQ2hhbmdlcyIsImFwaSIsInVybCIsIkNvbmZpZyIsInBieFVybCIsIm9uIiwic3VjY2Vzc1Rlc3QiLCJyZXNwb25zZSIsIk9iamVjdCIsImtleXMiLCJsZW5ndGgiLCJyZXN1bHQiLCJvblN1Y2Nlc3MiLCJjYkJlZm9yZVNlbmRGb3JtIiwic2V0dGluZ3MiLCJkYXRhIiwiZm9ybSIsImNiQWZ0ZXJTZW5kRm9ybSIsIkZvcm0iLCJnbG9iYWxSb290VXJsIiwiZG9jdW1lbnQiLCJyZWFkeSJdLCJtYXBwaW5ncyI6Ijs7QUFBQTs7Ozs7OztBQU1BO0FBRUEsSUFBTUEsbUJBQW1CLEdBQUc7QUFDM0JDLEVBQUFBLFFBQVEsRUFBRUMsQ0FBQyxDQUFDLDRCQUFELENBRGdCO0FBRTNCQyxFQUFBQSxVQUYyQjtBQUFBLDBCQUVkO0FBQ1o7QUFDQUgsTUFBQUEsbUJBQW1CLENBQUNJLGNBQXBCO0FBQ0E7O0FBTDBCO0FBQUE7O0FBTzNCOzs7QUFHQUMsRUFBQUEseUJBVjJCO0FBQUEseUNBVUM7QUFDM0JILE1BQUFBLENBQUMsQ0FBQ0ksR0FBRixDQUFNO0FBQ0xDLFFBQUFBLEdBQUcsWUFBS0MsTUFBTSxDQUFDQyxNQUFaLG9EQURFO0FBRUxDLFFBQUFBLEVBQUUsRUFBRSxLQUZDO0FBR0xDLFFBQUFBLFdBSEs7QUFBQSwrQkFHT0MsUUFIUCxFQUdpQjtBQUNyQjtBQUNBLG1CQUFPQyxNQUFNLENBQUNDLElBQVAsQ0FBWUYsUUFBWixFQUFzQkcsTUFBdEIsR0FBK0IsQ0FBL0IsSUFBb0NILFFBQVEsQ0FBQ0ksTUFBVCxLQUFvQixJQUEvRDtBQUNBOztBQU5JO0FBQUE7QUFPTEMsUUFBQUEsU0FQSztBQUFBLCtCQU9PLENBQ1g7QUFDQTs7QUFUSTtBQUFBO0FBQUEsT0FBTjtBQVdBOztBQXRCMEI7QUFBQTtBQXVCM0JDLEVBQUFBLGdCQXZCMkI7QUFBQSw4QkF1QlZDLFFBdkJVLEVBdUJBO0FBQzFCLFVBQU1ILE1BQU0sR0FBR0csUUFBZjtBQUNBSCxNQUFBQSxNQUFNLENBQUNJLElBQVAsR0FBY3BCLG1CQUFtQixDQUFDQyxRQUFwQixDQUE2Qm9CLElBQTdCLENBQWtDLFlBQWxDLENBQWQ7QUFDQSxhQUFPTCxNQUFQO0FBQ0E7O0FBM0IwQjtBQUFBO0FBNEIzQk0sRUFBQUEsZUE1QjJCO0FBQUEsK0JBNEJUO0FBQ2pCdEIsTUFBQUEsbUJBQW1CLENBQUNLLHlCQUFwQjtBQUNBOztBQTlCMEI7QUFBQTtBQStCM0JELEVBQUFBLGNBL0IyQjtBQUFBLDhCQStCVjtBQUNoQm1CLE1BQUFBLElBQUksQ0FBQ3RCLFFBQUwsR0FBZ0JELG1CQUFtQixDQUFDQyxRQUFwQztBQUNBc0IsTUFBQUEsSUFBSSxDQUFDaEIsR0FBTCxhQUFjaUIsYUFBZCwrQkFGZ0IsQ0FHaEI7O0FBQ0FELE1BQUFBLElBQUksQ0FBQ0wsZ0JBQUwsR0FBd0JsQixtQkFBbUIsQ0FBQ2tCLGdCQUE1QztBQUNBSyxNQUFBQSxJQUFJLENBQUNELGVBQUwsR0FBdUJ0QixtQkFBbUIsQ0FBQ3NCLGVBQTNDO0FBQ0FDLE1BQUFBLElBQUksQ0FBQ3BCLFVBQUw7QUFDQTs7QUF0QzBCO0FBQUE7QUFBQSxDQUE1QjtBQTBDQUQsQ0FBQyxDQUFDdUIsUUFBRCxDQUFELENBQVlDLEtBQVosQ0FBa0IsWUFBTTtBQUN2QjFCLEVBQUFBLG1CQUFtQixDQUFDRyxVQUFwQjtBQUNBLENBRkQiLCJzb3VyY2VzQ29udGVudCI6WyIvKlxuICogQ29weXJpZ2h0IMKpIE1JS08gTExDIC0gQWxsIFJpZ2h0cyBSZXNlcnZlZFxuICogVW5hdXRob3JpemVkIGNvcHlpbmcgb2YgdGhpcyBmaWxlLCB2aWEgYW55IG1lZGl1bSBpcyBzdHJpY3RseSBwcm9oaWJpdGVkXG4gKiBQcm9wcmlldGFyeSBhbmQgY29uZmlkZW50aWFsXG4gKiBXcml0dGVuIGJ5IEFsZXhleSBQb3J0bm92LCAxMiAyMDE4XG4gKi9cbi8qIGdsb2JhbCBnbG9iYWxSb290VXJsLENvbmZpZywgRm9ybSAqL1xuXG5jb25zdCBtb2R1bGVBdXRvcHJvdmlzaW9uID0ge1xuXHQkZm9ybU9iajogJCgnI21vZHVsZS1hdXRvcHJvdmlzaW9uLWZvcm0nKSxcblx0aW5pdGlhbGl6ZSgpIHtcblx0XHQvLyDQlNC40L3QsNC80LjRh9C10YHQutCw0Y8g0L/RgNC+0LLQtdGA0LrQsCDRgdCy0L7QsdC+0LTQtdC9INC70Lgg0LLQvdGD0YLRgNC10L3QvdC40Lkg0L3QvtC80LXRgFxuXHRcdG1vZHVsZUF1dG9wcm92aXNpb24uaW5pdGlhbGl6ZUZvcm0oKTtcblx0fSxcblxuXHQvKipcbiAgICAgKiDQn9GA0LjQvNC10L3QtdC90LjQtSDQvdCw0YHRgtGA0L7QtdC6INC80L7QtNGD0LvRjyDQv9C+0YHQu9C1INC40LfQvNC10L3QtdC90LjRjyDQtNCw0L3QvdGL0YUg0YTQvtGA0LzRi1xuICAgICAqL1xuXHRhcHBseUNvbmZpZ3VyYXRpb25DaGFuZ2VzKCkge1xuXHRcdCQuYXBpKHtcblx0XHRcdHVybDogYCR7Q29uZmlnLnBieFVybH0vcGJ4Y29yZS9hcGkvbW9kdWxlcy9Nb2R1bGVBdXRvcHJvdmlzaW9uL3JlbG9hZGAsXG5cdFx0XHRvbjogJ25vdycsXG5cdFx0XHRzdWNjZXNzVGVzdChyZXNwb25zZSkge1xuXHRcdFx0XHQvLyB0ZXN0IHdoZXRoZXIgYSBKU09OIHJlc3BvbnNlIGlzIHZhbGlkXG5cdFx0XHRcdHJldHVybiBPYmplY3Qua2V5cyhyZXNwb25zZSkubGVuZ3RoID4gMCAmJiByZXNwb25zZS5yZXN1bHQgPT09IHRydWU7XG5cdFx0XHR9LFxuXHRcdFx0b25TdWNjZXNzKCkge1xuXHRcdFx0XHQvLyBtb2R1bGVBdXRvcHJvdmlzaW9uLnRlc3RDb25uZWN0aW9uKCk7XG5cdFx0XHR9LFxuXHRcdH0pO1xuXHR9LFxuXHRjYkJlZm9yZVNlbmRGb3JtKHNldHRpbmdzKSB7XG5cdFx0Y29uc3QgcmVzdWx0ID0gc2V0dGluZ3M7XG5cdFx0cmVzdWx0LmRhdGEgPSBtb2R1bGVBdXRvcHJvdmlzaW9uLiRmb3JtT2JqLmZvcm0oJ2dldCB2YWx1ZXMnKTtcblx0XHRyZXR1cm4gcmVzdWx0O1xuXHR9LFxuXHRjYkFmdGVyU2VuZEZvcm0oKSB7XG5cdFx0bW9kdWxlQXV0b3Byb3Zpc2lvbi5hcHBseUNvbmZpZ3VyYXRpb25DaGFuZ2VzKCk7XG5cdH0sXG5cdGluaXRpYWxpemVGb3JtKCkge1xuXHRcdEZvcm0uJGZvcm1PYmogPSBtb2R1bGVBdXRvcHJvdmlzaW9uLiRmb3JtT2JqO1xuXHRcdEZvcm0udXJsID0gYCR7Z2xvYmFsUm9vdFVybH1tb2R1bGUtYXV0b3Byb3Zpc2lvbi9zYXZlYDtcblx0XHQvLyBGb3JtLnZhbGlkYXRlUnVsZXMgPSBtb2R1bGVBdXRvcHJvdmlzaW9uLnZhbGlkYXRlUnVsZXM7XG5cdFx0Rm9ybS5jYkJlZm9yZVNlbmRGb3JtID0gbW9kdWxlQXV0b3Byb3Zpc2lvbi5jYkJlZm9yZVNlbmRGb3JtO1xuXHRcdEZvcm0uY2JBZnRlclNlbmRGb3JtID0gbW9kdWxlQXV0b3Byb3Zpc2lvbi5jYkFmdGVyU2VuZEZvcm07XG5cdFx0Rm9ybS5pbml0aWFsaXplKCk7XG5cdH0sXG59O1xuXG5cbiQoZG9jdW1lbnQpLnJlYWR5KCgpID0+IHtcblx0bW9kdWxlQXV0b3Byb3Zpc2lvbi5pbml0aWFsaXplKCk7XG59KTtcbiJdfQ==