function benchmark_get_fields(model) {
	model_fields = [];

	var settings = model.get('settings');
	var models = settings.attributes.form_fields['models'];

	model_fields.push({ id: '0', label: '- None -' });
	var model_length = models.length;
	for (var i = 0; i < model_length; i++) {
		model_fields.push({
			id: models[i].attributes.custom_id,
			label: models[i].attributes.field_label,
			type: models[i].attributes.field_type,
		});
	}
	return model_fields;
}

function init_benchmarkemail(panel, model) {
	custom_fields = [];
	var list_id = panel.$el.find('.elementor-control-ebma_list select').val();
	panel.$el.find('[class*=elementor-control-ebma_form_]').hide();
	if (!list_id) {
		return;
	}
	panel.$el.find('.ebma_loader_gif').show();

	var data = {
		list_id: list_id,
		action: 'get_benchmarkemail_custom_fields',
	};

	jQuery.ajax({
		type: 'GET',
		url: php_vars.ajax_url,
		data: data,
		dataType: 'json',
		success: function (response) {
			if (response.status == 'fail') {
				return;
			}
			benchmark_show_load_dynamic_custom_fields(
				panel,
				model,
				response.custom_fields
			);
			panel.$el.find('.ebma_loader_gif').hide();
		},
		error: function (error) {
			console.log('bad');
		},
	});
}

function benchmark_show_load_dynamic_custom_fields(
	panel,
	model,
	custom_fields
) {
	model_fields = benchmark_get_fields(model);
	panel.$el
		.find('[class*=elementor-control-ebma_form_]')
		.slice(0, custom_fields.length)
		.each(function (index) {
			// Show the Field.
			jQuery(this).show();

			var label = 'label_' + index;
			jQuery(this)
				.find('.elementor-control-title')
				.html(custom_fields[index])
				.attr('label', label);
			// Load the Fields in the Front end.

			benchmark_update_field_mapping(
				panel,
				model,
				['ebma_form_' + index],
				model_fields
			);
		});
}

function benchmark_update_field_mapping(panel, model, options, model_fields) {
	var settings = model.get('settings');
	var option_length = options.length;
	for (var op = 0; op < option_length; op++) {
		var local_fields = JSON.parse(JSON.stringify(model_fields));
		var selector = '.elementor-control-' + options[op];
		var label_name = panel.$el
			.find(selector)
			.find('.elementor-control-title')
			.attr('label');
		var local_fields_length = local_fields.length;
		for (var m = 1; m < local_fields_length; m++) {
			local_fields[m]['id2'] = local_fields[m]['id'];
			local_fields[m]['id'] = local_fields[m]['id'] + '-(' + label_name + ')';
		}
		panel.$el.find(selector + ' select').empty();
		benchmark_load_select_option(panel, local_fields, selector);

		if (settings.attributes[options[op]] != '') {
			var setting_val = settings.attributes[options[op]];

			if (
				panel.$el.find(selector + ' select option[value="' + setting_val + '"]')
					.length > 0
			) {
				panel.$el.find(selector + ' select').val(setting_val);
			} else {
				panel.$el.find(selector + ' select').val(0);
			}
		}
	}
}
function benchmark_load_select_option(panel, data, cls) {
	var data_length = data.length;
	for (var i = 0; i < data_length; i++) {
		var value = data[i].label;
		if (value == '') {
			value = data[i].id2;
		}

		panel.$el
			.find(cls + ' select')
			.append(
				jQuery('<option></option>').attr('value', data[i].id).text(value)
			);
	}
}

if (typeof elementor !== 'undefined') {
	elementor.hooks.addAction('panel/open_editor/widget/form', function (
		panel,
		model,
		view
	) {
		panel.$el
			.find('#elementor-controls')
			.on(
				'DOMNodeInserted',
				'.elementor-control-elementor_benchmarkemail_section',
				function () {
					var list_id = panel.$el
						.find('.elementor-control-ebma_list select')
						.val();
					if (list_id == '') {
						panel.$el.find('[class*=elementor-control-ebma_form_]').hide();
					}
					setTimeout(function () {
						init_benchmarkemail(panel, model);
					}, 200);
				}
			);

		init_benchmarkemail(panel, model);
	});
}

elementor.channels.editor.on('change', function (controlView, elementView) {
	if ('ebma_list' === controlView.model.get('name')) {
		init_benchmarkemail(elementor.panel, elementView.model);
	}
});
