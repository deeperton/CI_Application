<?php

/**
 * @author Artyuh Anton <deeperton@gmail.com>
 * @copyright MID 2009
 */

$config['theme_patch'] = APPPATH . 'views'; //папка с шаблонами
$config['theme'] = 'default';             //основной шаблон для пользователей

$config['base_style_order'] = 'last';//означает, что базовые стили добаляються последними. остальные -- по мере вызова (first -- первым)

/*
 * Скрепление файлов
 *
 * Стили
 */

/*
 * скреплять ли файлы стилей
 */
$config['compile_styles'] = false;
$config['title_delimiter'] = ' - ';
/*
 * полноформатерный патерн регулярного выражения. Какие файлы исключать при скреплении.
 */
$config['style_no_compile_for_mask'] = "#^(jquery|ui)#i";

/*
 * куда ложить собранный файл
 */
$config['style_sum_dir'] = 'css/c';

/*
 * папка с ресурсами видимая по http
 */
$config['rc_folder'] = 'rc';

$config['doctype'] = 'html4-trans'; // http://codeigniter.com/user_guide/helpers/html_helper.html#doctype
