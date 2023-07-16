<?php

namespace Braine;

class Braine_MultiStepForm_Widget_Loader {
    private static $_instance = null;

    public static function instance() {
        if(is_null(self::$_instance)):
            self::$_instance = new self();
        endif;

        return self::$_instance;
    }

    public function include_widgets_files() {
        require_once __DIR__ . '/multistep-form/multistep-form.php';
    }

    public function register_widgets() {
        $this->include_widgets_files();

        \Elementor\Plugin::instance()->widgets_manager->register(new Widgets\MultiStep_Form());
    }

    public function styles_editor_widget() {
        wp_register_style('braine_multistep_form-editor', plugins_url('/multistep-form/assets/css/multistep-form-editor.css', __FILE__));

        wp_enqueue_style('braine_multistep_form-editor');
    }

    public function widget_callback() {
        $getPost = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        $setPost = array_map('strip_tags', $getPost["dados"]);
        $Post = array_map('trim', $setPost);

        // explode without decode
        $Questions = explode('&', $Post['serializedData']);
        $QuestionsMap = [];

        foreach($Questions as $Question):

            $ExplodedQuestion = explode('=', $Question);
            $QuestionTitle = urldecode($ExplodedQuestion[0]);
            $QuestionAnswer = urldecode($ExplodedQuestion[1]);

            $QuestionsMap[$QuestionTitle] = $QuestionAnswer;

        endforeach;

        $HTML_Email = "De: {$QuestionsMap['Nome']} <[{$QuestionsMap['Email']}]><br /><br />";
        $HTML_Email .= "O usuário (a) <b>{$QuestionsMap['Nome']}</b> acessou o site Braine e preencheu o formulário.<br /><br />";
        $HTML_Email .= "<b>INFORMAÇÕES ENVIADAS:</b><br /><br />";
        $HTML_Email .= "<b>Nome:</b> {$QuestionsMap['Nome']}<br />";
        $HTML_Email .= "<b>Email:</b> {$QuestionsMap['Email']}<br />";
        $HTML_Email .= "<b>Telefone:</b> {$QuestionsMap['Telefone']}<br />";
        $HTML_Email .= "<b>Estado:</b> {$QuestionsMap['Estado']}<br />";
        $HTML_Email .= "<b>Cidade:</b> {$QuestionsMap['Cidade']}<br />";
        $HTML_Email .= "<b>Bairro:</b> {$QuestionsMap['Bairro']}<br /><br />";

        unset($QuestionsMap['Nome'], $QuestionsMap['Email'], $QuestionsMap['Telefone'], $QuestionsMap['Estado'], $QuestionsMap['Cidade'],$QuestionsMap['Bairro']);

        foreach ($QuestionsMap as $Question => $Answer):

            $Question = str_replace(':', '', $Question);
            $HTML_Email .= "<b>{$Question}:</b> {$Answer} <br />";
        endforeach;

        wp_mail($Post['sendEmail'], $Post['titleEmail'], $HTML_Email, ['Content-Type: text/html; charset=UTF-8']);

        wp_send_json_success([
            'message' => $HTML_Email,
        ]);
    }

    public function register_category($elements_manager) {
        $elements_manager->add_category(
            'braine',
            [
                'title' => "Braine",
                'icon' => 'fa fa-plug',
            ]
        );
    }

    public function init() {
        add_action('elementor/editor/after_enqueue_styles', [$this, 'styles_editor_widget']);

        add_action('elementor/widgets/widgets_registered', [$this, 'register_widgets'], 99);
        add_action('elementor/elements/categories_registered', [$this, 'register_category']);

        add_action('wp_ajax_braine_multistep_handle_form', [$this,  'widget_callback']);
        add_action('wp_ajax_nopriv_braine_multistep_handle_form', [$this,  'widget_callback']);
    }

    public function __construct() {
        add_action('plugins_loaded', [$this, 'init']);
    }
}

Braine_MultiStepForm_Widget_Loader::instance();
