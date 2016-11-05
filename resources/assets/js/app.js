
/**
 * First we will load all of this project's JavaScript dependencies which
 * include Vue and Vue Resource. This gives a great starting point for
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

Vue.component('example', require('./components/Example.vue'));

const app = new Vue({
});

$(document).ready(function () {
    $('[data-click-input-name]').on('click', function () {
        $('[name="' + $(this).attr('data-click-input-name') + '"]').val($(this).attr('data-click-input-value'));
    });
    $('[data-submit-on-click]').on('click', function () {
        $(this).parents('form').submit();
    });
    $('[data-submit-on-change]').on('change', function () {
        $(this).parents('form').submit();
    });
});
