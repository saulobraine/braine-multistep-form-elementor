<?php

namespace Braine\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if(!defined('ABSPATH')) {
    exit;
}

class MultiStep_Form extends Widget_Base {
    public function __construct($data = [], $args = null) {
        parent::__construct($data, $args);

        wp_register_style('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', null, null);

        wp_register_style('braine_multistep_form', plugins_url('/assets/css/multistep-form.css', __FILE__), ['elementor-frontend', 'select2']);

        wp_register_script('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', null, null);
        wp_register_script('select2-pt_BR', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/pt-BR.js', ['select2'], null);
        wp_register_script('jquery-mask', 'https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js', ['jquery'], null);

        wp_register_script('braine_multistep_form', plugins_url('/assets/js/multistep-form.js', __FILE__), ['jquery', 'elementor-frontend', 'select2', 'select2-pt_BR', 'jquery-mask'], '1.0.0', true);

        wp_localize_script(
            'braine_multistep_form',
            'braine_multistep_form_ajax',
            ['ajax_url' => admin_url('admin-ajax.php')]
        );
    }

    public function get_script_depends() {
        return ['braine_multistep_form'];
    }

    public function get_style_depends() {
        return ['braine_multistep_form'];
    }

    public function get_name() {
        return 'braine-multistep-form';
    }

    public function get_title() {
        return 'MultiStep Form';
    }

    public function get_icon() {
        return 'eicon-slides';
    }

    public function get_categories() {
        return ['braine'];
    }

    protected function _register_controls() {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Perguntas', 'multistep-form-braine'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $repeater = new \Elementor\Repeater();

        $repeater->add_control(
            'question_title',
            [
                'label' => __('Título', 'multistep-form-braine'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Título', 'multistep-form-braine'),
                'label_block' => true,
            ]
        );

        $repeater->add_control(
            'question_type',
            [
                'label' => __('Tipo', 'multistep-form-braine'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => __('Opções', 'multistep-form-braine'),
                'options' => [
                    'options' => __('Opções', 'multistep-form-braine'),
                    'textarea' => __('Área de texto', 'multistep-form-braine'),
                    'dimensions' => __('Dimensões', 'multistep-form-braine'),
                    'lead_step' => __('Questionário final', 'multistep-form-braine')
                ]
            ]
        );

        $repeater->add_control(
            'question_options',
            [
                'label' => __('Opções', 'multistep-form-braine'),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'description' => __('Cada opção deve estar em uma linha', 'multistep-form-braine'),
                'label_block' => true,
                'condition' => [
                    'question_type' => 'options'
                ],
                'default' => [
                    'Opção',
                ]
            ],
        );

        $repeater->add_control(
            'question_dimensions',
            [
                'label' => __('Dimensões', 'multistep-form-braine'),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'description' => __('Cada campo deve estar em uma linha.', 'multistep-form-braine'),
                'label_block' => true,
                'default' => [
                    'Dimensão',
                ],
                'condition' => [
                    'question_type' => 'dimensions'
                ]
            ],
        );

        $repeater->add_control(
            'lead_step_important_note',
            [
                'label' => __('Importante', 'multistep-form-braine'),
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => __('Adicione o tipo "Questionário Final" apenas <strong>UMA</strong> vez.', 'multistep-form-braine'),
                'content_classes' => 'braine-alert',
                'condition' => [
                    'question_type' => 'lead_step'
                ],
                'show_label' => false,
            ]
        );

        $this->add_control(
            'questions',
            [
                'label' => __('Cadastro de Questões', 'multistep-form-braine'),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'question_title' => __('Pergunta padrão', 'multistep-form-braine'),
                        'question_type' => 'options'
                    ],
                ],
                'title_field' => '{{{ question_title }}}',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'configuration_section',
            [
                'label' => __('Configurações', 'multistep-form-braine'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'configuration_mail',
            [
                'label' => __('Email', 'elementor-pro'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'send_email',
            [
                'label' => __('E-mail que irá receber', 'multistep-form-braine'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('contato@braine.com.br', 'multistep-form-braine'),
                'placeholder' => __('Digite o e-mail', 'multistep-form-braine'),
                'label_block' => true,
                'frontend_available' => true
            ]
        );

        $this->add_control(
            'title_email',
            [
                'label' => __('Título do E-mail', 'multistep-form-braine'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Nova interação braine - Formulário', 'multistep-form-braine'),
                'placeholder' => __('Digite o título do e-mail', 'multistep-form-braine'),
                'label_block' => true,
                'frontend_available' => true
            ]
        );

        $this->add_control(
            'configuration_text',
            [
                'label' => __('Textos', 'elementor-pro'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'finalstep_text_title',
            [
                'label' => __('Título - Etapa Final', 'multistep-form-braine'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('OBRIGADO PELAS RESPOSTAS :)', 'multistep-form-braine'),
                'placeholder' => __('Digite o título', 'multistep-form-braine'),
                'label_block' => true,
                'frontend_available' => true
            ]
        );

        $this->add_control(
            'finalstep_text_description',
            [
                'label' => __('Descrição - Etapa Final', 'multistep-form-braine'),
                'type' => \Elementor\Controls_Manager::WYSIWYG,
                'default' => __('LOGO MAIS ENTRAREMOS EM CONTATO PARA CONVERSAR MELHOR COM VOCÊ SOBRE O SEU PROJETO!', 'multistep-form-braine'),
                'placeholder' => __('Digite a descrição', 'multistep-form-braine'),
                'label_block' => true,
                'frontend_available' => true
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $id_element = $this->get_id();

        ?>
<?php

        if ($settings['questions']):
            echo "<form method='POST' name='braine-multistep-form-{$id_element}' class='braine-multistep-form id-{$id_element}'>";
            foreach ($settings['questions'] as $key => $item):
                $key++;
                $total_questions = count($settings['questions']);
                ?>

<div id="<?= $item['_id'] ?>" class="step <?= ($key === 1) ? 'current' : ''; ?>">
  <?php if($key !== 1):	?>
  <button class='prev-step'><span class="icon"><img
        src="<?= plugins_url("/assets/icons/chevron-backward.svg", __FILE__) ?>"
        alt="Voltar"></span><?= __("Voltar?", 'multistep-form-braine') ?></button>
  <?php	endif; ?>
  <header>
    <div class="total-steps"><?= $key ?> de <?= $total_questions ?></div>
    <h3><?= $item['question_title'] ?></h3>
  </header>
  <article>
    <?php
                switch ($item['question_type']):
                    case 'options':
                        $options = preg_split("/\\r\\n|\\r|\\n/", $item['question_options']);
                        echo "<div class='braine-options'>";
                        foreach($options as $option):
                            ?>
    <input type="radio" id="option-<?= $item['_id'] ?>-<?= $option ?>" value="<?= $option ?>"
      name="<?= $item['question_title'] ?>">
    <label for="option-<?= $item['_id'] ?>-<?= $option ?>"><?= $option ?></label>
    <?php
                        endforeach;
                        echo "</div>";

                        break;

                    case 'dimensions':
                        $dimensions = preg_split("/\\r\\n|\\r|\\n/", $item['question_dimensions']);
                        echo "<div class='braine-dimensions'>";
                        foreach($dimensions as $dimension):
                            $dimensionsInput[] = "<input type='text' id='dimension-{$item['_id']}-{$dimension}' name='{$dimension}'
							placeholder='{$dimension}'>";
                        endforeach;

                        echo implode(" <span>X</span> ", $dimensionsInput);

                        echo "</div>";
                        echo "<span class='messages'></span>";
                        echo "<button class='next-step'>" . __("Enviar", 'multistep-form-braine') . "</button>";
                        break;

                    case 'textarea':
                        echo "<textarea name='{$item["question_title"]}'></textarea>";
                        echo "<span class='messages'></span>";
                        echo "<button class='next-step'>" . __("Enviar", 'multistep-form-braine') . "</button>";
                        break;

                    case 'lead_step':
                        ?>

    <div class="row">

      <div class="select-block">
        <select name="<?= __("Estado", "multistep-form-braine") ?>" class="estados">
          <option value="" hidden><?= __("Qual o estado?", "multistep-form-braine") ?></option>
        </select>
      </div>

      <div class="select-block">
        <select name="<?= __("Cidade", "multistep-form-braine") ?>" class="cidades">
          <option value="" hidden><?= __("Qual a cidade?", "multistep-form-braine") ?></option>
        </select>
      </div>

      <div class="input-block">
        <input type="text" name="Bairro" placeholder="<?= __("Qual o bairro?", "multistep-form-braine") ?>">
      </div>
      <div class="input-block">
        <input type="text" name="Nome" placeholder="<?= __("Qual seu nome?", "multistep-form-braine") ?>">
      </div>

      <div class="input-block">
        <input type="email" name="Email" placeholder="<?= __("Qual seu e-mail?", "multistep-form-braine") ?>">
      </div>
      <div class="input-block">
        <input type="text" class="phone-number" name="Telefone"
          placeholder="<?= __("Qual o seu telefone?", "multistep-form-braine") ?>">
      </div>
    </div>
    <span class='messages'></span>
    <button type="submit" class="submit"><?= __("Enviar", 'multistep-form-braine') ?></button>

    <?php
                    break;
                endswitch;
                ?>
</div>

<?php
            endforeach;

            ?>

<div class="step">
  <header>
    <h3><?= $settings['finalstep_text_title'] ?></h3>
  </header>
  <article>
    <p><?= $settings['finalstep_text_description'] ?></p>
  </article>
</div>
</article>
</form>
<?php
        endif;
    }
}
