class MultiStepFormElementor extends elementorModules.frontend.handlers.Base {

  getDefaultSettings() {
    return {
      selectors: {
        form: '.braine-multistep-form',
        step: '.step',
        nextButton: '.braine-options label, .next-step',
        prevButton: '.prev-step',
        submitButton: '[type=submit], .submit',
        selectEstados: '.estados',
        selectCidades: '.cidades'
      },
    };
  }

  getDefaultElements() {
    const selectors = this.getSettings('selectors');
    return {
      $form: this.$element.find(selectors.form),
      $step: this.$element.find(selectors.step),
      $nextButton: this.$element.find(selectors.nextButton),
      $prevButton: this.$element.find(selectors.prevButton),
      $submitButton: this.$element.find(selectors.submitButton),
      $selectEstados: this.$element.find(selectors.selectEstados),
      $selectCidades: this.$element.find(selectors.selectCidades)
    }
  }

  bindEvents() {
    this.elements.$nextButton.on('click', this.nextStepEvent.bind(this));
    this.elements.$prevButton.on('click', this.prevStepEvent.bind(this));
    this.elements.$selectEstados.on('change', this.loadCities.bind(this));
    this.elements.$form.on('submit', this.preventSubmit.bind(this));
    this.elements.$submitButton.on('click', this.submitEvent.bind(this));
  }

  // Init

  onInit() {
    elementorModules.frontend.handlers.Base.prototype.onInit.apply(this, arguments);

    const selectEstados = this.elements.$selectEstados;
    const selectCidades = this.elements.$selectCidades;

    jQuery.getJSON('https://gist.githubusercontent.com/letanure/3012978/raw/2e43be5f86eef95b915c1c804ccc86dc9790a50a/estados-cidades.json', function (response) {
      const { estados } = response;

      estados.map(estado => {
        selectEstados.append(`<option value="${estado.nome}">${estado.nome}</option>`);
      });

      selectEstados.select2({
        width: '100%',
        language: 'pt-BR'
      });

      selectCidades.select2({
        width: '100%',
        language: 'pt-BR'
      });
    });

    this.elements.$form.find('.phone-number').mask("(99) 99999.9999");

  }

  // Methods

  nextStepEvent() {
    if (!this.verifyInputs()) this.$element.find(`.step.current`).removeClass('current').next().addClass('current');
  }

  prevStepEvent(event) {
    event.preventDefault();
    this.$element.find(`.step.current`).removeClass('current').prev().addClass('current');
  }

  loadCities() {

    const selectCidades = this.elements.$selectCidades;
    const EstadoSelecionado = this.elements.$selectEstados.val();

    if (selectCidades.data('select2') !== undefined) {
      selectCidades.select2('destroy');
    }

    selectCidades.html('<option>...</option>');

    jQuery.getJSON('https://gist.githubusercontent.com/letanure/3012978/raw/2e43be5f86eef95b915c1c804ccc86dc9790a50a/estados-cidades.json', function (response) {
      const { estados } = response;

      const filteredEstado = estados.filter(estado => estado.nome === EstadoSelecionado);

      const { cidades } = filteredEstado[0];

      selectCidades.html('');


      cidades.map(cidade => {
        selectCidades.append(`<option value="${cidade}">${cidade}</option>`);
      });

      selectCidades.select2({
        width: '100%',
        language: 'pt-BR'
      });
    });
  }

  preventSubmit(event) {
    event.preventDefault();
  }

  submitEvent(event) {
    event.preventDefault();

    const element = this.$element;

    if (!this.verifyInputs()) {
      const dados = {
        serializedData: this.$element.find(`.braine-multistep-form`).serialize(),
        sendEmail: this.getElementSettings('send_email'),
        titleEmail: this.getElementSettings('title_email'),
      }

      jQuery.ajax({
        url: braine_multistep_form_ajax.ajax_url,
        data: {
          action: "braine_multistep_handle_form",
          dados
        },
        type: 'POST',
        dataType: 'json',
        beforeSend: () => {
          element.find(`.step.current`).css({
            'transition': '.2s all',
            'opacity': '0.5'
          });
        }
      }).done(() => {
        element.find(`.step.current`).removeClass('current').next().addClass('current');
      });
    }
  }

  verifyInputs() {

    let emptyInputs = [];

    this.$element.find(`.step.current input[type=text], .step.current input[type=email], .step.current select, .step.current textarea`).filter((index, input) => {
      input.value === "" && emptyInputs.push(input.name);
    });

    if (emptyInputs.length !== 0) {
      let message;
      switch (emptyInputs.length) {
        case 1:
          message = `O campo <b>${emptyInputs[0]}</b> precisa estar preenchido.`;
          break;

        case 2:
          message = 'Os campos <b>' + emptyInputs.join(' e ') + '</b> precisam estar preenchidos.';
          break;

        default:
          message = 'Os campos <b>' + emptyInputs.slice(0, -1).join(', ') + ' e ' + emptyInputs.slice(-1) + '</b> precisam estar preenchidos.';
          break;
      }
      this.$element.find(`.step.current .messages`).html(message).fadeIn();

      return true;
    }

    this.$element.find(`.step.current .messages`).fadeOut().html('');

    return false;
  }
}

jQuery(window).on('elementor/frontend/init', () => {
  const addHandler = ($element) => {
    elementorFrontend.elementsHandler.addHandler(MultiStepFormElementor, {
      $element,
    });
  };

  elementorFrontend.hooks.addAction('frontend/element_ready/braine-multistep-form.default', addHandler);
});