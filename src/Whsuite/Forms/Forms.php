<?php

namespace Whsuite\Forms;

use Illuminate\Support\Str;

class Forms
{
    /**
     * form builder instance
     */
    protected static $instance;

    /**
     * input element defaults
     *
     */
    protected $input_defaults = array(
        'text' => array(
            'wrap' => 'div',
            'wrap_class' => 'form-group',
            'before' => '',
            'between' => '',
            'after' => '',
            'label' => array(),
            'type' => 'text',
            'class' => 'form-control',
            'options' => array(),
            'hidden_checkbox' => false
        ),
        'checkbox' => array(
            'wrap' => 'div',
            'wrap_class' => 'checkbox',
            'before' => '',
            'between' => '',
            'after' => '',
            'label' => array(),
            'type' => 'checkbox',
            'value' => '1',
            'class' => false,
            'options' => array(),
            'hidden_checkbox' => true
        ),
        'radio' => array(
            'wrap' => 'div',
            'wrap_class' => 'radio',
            'before' => '',
            'between' => '',
            'after' => '',
            'label' => array(),
            'type' => 'radio',
            'value' => '1',
            'class' => 'radio',
            'options' => array(),
            'hidden_checkbox' => true
        ),
        'hidden' => array(
            'wrap' => false,
            'wrap_class' => null,
            'before' => '',
            'between' => '',
            'after' => '',
            'label' => array(),
            'type' => 'hidden',
            'class' => false,
            'options' => array(),
            'hidden_checkbox' => false
        ),
        'submit' => array(
            'wrap' => false,
            'wrap_class' => null,
            'before' => '',
            'between' => '',
            'after' => '',
            'label' => array(),
            'type' => 'submit',
            'class' => 'btn btn-primary',
            'options' => array(),
            'hidden_checkbox' => false
        ),
        'button' => array(
            'wrap' => false,
            'wrap_class' => null,
            'before' => '',
            'between' => '',
            'after' => '',
            'label' => array(),
            'type' => 'button',
            'class' => 'btn',
            'options' => array(),
            'hidden_checkbox' => false
        )
    );

    /**
     * array of params to remove from the input param list
     *
     */
    protected $param_remove_list = array(
        'wrap' => 'wrap',
        'wrap_class' => 'wrap_class',
        'before' => 'before',
        'between' => 'between',
        'after' => 'after',
        'label' => 'label',
        'options' => 'options',
        'hidden_checkbox' => 'hidden_checkbox'
    );

    /**
     * array of parameters not in the var=value attribute format
     */
    protected $param_attr_exceptions = array(
        'checked',
        'disabled',
        'multiple',
        'selected',
        'readonly'
    );

    /**
     * init the form builder and and return instance
     */
    public static function init()
    {
        // init the input handler
        \Whsuite\Inputs\Inputs::init();

        // start the form handler and return the instance
        self::$instance = new \Whsuite\Forms\Forms;

        // Check for App and add to the view
        if (\App::check('view')) {
            \App::get('view')->set('forms', self::$instance);
        }

        return self::instance();
    }

    /**
     * get the instance
     *
     */
    public static function instance()
    {
        return self::$instance;
    }

    /**
     * open a form
     *
     * @param   array  array of data for the form tag - action / method only required elements
     *                 (other elements will be compiled into key="value" attributes)
     * @return  string
     */
    public function open(array $params)
    {
        if (! isset($params['method']) || ! in_array($params['method'], array('get', 'post', 'files'))) {
            $params['method'] = 'post';
        }

        if ($params['method'] == 'files') {
            $params['method'] = 'post';
            $params['enctype'] = 'multipart/form-data';
        }

        if (! isset($params['action'])) {
            $params['action'] = '';
        }

        if (! isset($params['class'])) {
            $params['class'] = 'form-horizontal';
        }

        $attributes = $this->compileAttributes($params);

        $csrf_field = $this->hidden(
            '__csrf_value',
            array(
                'value' => \App::get('session')->getCsrfToken()->getValue()
            )
        );

        return '<form ' . $attributes . '>'.$csrf_field;
    }

    /**
     * close the form
     *
     * @return  string
     */
    public function close()
    {
        return '</form>';
    }

    /**
     * alias functions for input
     *
     */

    /**
     * generate a hidden input
     *
     * @param   string  dot notation form for input - will match with Input handler and prefill if found
     * @param   string  label for form, pass false to not show label
     * @param   array   array of extra options for the input
     * @return  string  form element
     */
    public function hidden($field_name, $params = array())
    {
        return $this->input($field_name, false, array_merge($params, array('type' => 'hidden')));
    }

    /**
     * generate a password input
     *
     * @param   string  dot notation form for input - will match with Input handler and prefill if found
     * @param   string  label for form, pass false to not show label
     * @param   array   array of extra options for the input
     * @return  string  form element
     */
    public function password($field_name, $label, $params = array())
    {
        return $this->input($field_name, $label, array_merge($params, array('type' => 'password')));
    }

    /**
     * generate a file input
     *
     * @param   string  dot notation form for input - will match with Input handler and prefill if found
     * @param   string  label for form, pass false to not show label
     * @param   array   array of extra options for the input
     * @return  string  form element
     */
    public function file($field_name, $label, $params = array())
    {
        return $this->input($field_name, $label, array_merge($params, array('type' => 'file')));
    }

    /**
     * generate a textarea input
     *
     * @param   string  dot notation form for input - will match with Input handler and prefill if found
     * @param   string  label for form, pass false to not show label
     * @param   array   array of extra options for the input
     * @return  string  form element
     */
    public function textarea($field_name, $label, $params = array())
    {
        return $this->input($field_name, $label, array_merge($params, array('type' => 'textarea')));
    }

    /**
     * generate a wysiwyg input
      *
      * @param   string  dot notation form for input - will match with Input handler and prefill if found
      * @param   string  label for form, pass false to not show label
      * @param   array   array of extra options for the input
      * @return  string  form element
      */
    public function wysiwyg($field_name, $label, $params = array())
    {
        return $this->input($field_name, $label, array_merge($params, array('type' => 'textarea', 'wysiwyg' => true)));
    }

    /**
     * generate a select box
     *
     * @param   string  dot notation form for input - will match with Input handler and prefill if found
     * @param   string  label for form, pass false to not show label
     * @param   array   array of extra options for the input
     * @return  string  form element
     */
    public function select($field_name, $label, $params = array())
    {
        return $this->input($field_name, $label, array_merge($params, array('type' => 'select')));
    }

    /**
     * generate a select box
     *
     * @param   string  dot notation form for input - will match with Input handler and prefill if found
     * @param   string  label for form, pass false to not show label
     * @param   array   array of extra options for the input
     * @return  string  form element
     */
    public function multiselect($field_name, $label, $params = array())
    {
        return $this->input($field_name.'.', $label, array_merge($params, array('type' => 'select', 'multiple' => 'multiple')));
    }

    /**
     * generate a checkbox input
     *
     * @param   string  dot notation form for input - will match with Input handler and prefill if found
     * @param   string  label for form, pass false to not show label
     * @param   array   array of extra options for the input
     * @return  string  form element
     */
    public function checkbox($field_name, $label, $params = array())
    {
        return $this->input($field_name, $label, array_merge($params, array('type' => 'checkbox')));
    }

    /**
     * generate a radio button input
     *
     * @param   string  dot notation form for input - will match with Input handler and prefill if found
     * @param   string  label for form, pass false to not show label
     * @param   array   array of extra options for the input
     * @return  string  form element
     */
    public function radio($field_name, $label, $params = array())
    {
        return $this->input($field_name, $label, array_merge($params, array('type' => 'radio')));
    }

    /**
     * generate a submit input
     *
     * @param   string  dot notation form for input - will match with Input handler and prefill if found
     * @param   string  label for form, pass false to not show label
     * @param   array   array of extra options for the input
     * @return  string  form element
     */
    public function submit($field_name, $label, $params = array())
    {
        return $this->input($field_name, false, array_merge($params, array('type' => 'submit', 'value' => $label)));
    }

    /**
     * generate a button input
     *
     * @param   string  dot notation form for input - will match with Input handler and prefill if found
     * @param   string  label for form, pass false to not show label
     * @param   array   array of extra options for the input
     * @return  string  form element
     */
    public function button($field_name, $label, $params = array())
    {
        return $this->input($field_name, false, array_merge($params, array('type' => 'button', 'value' => $label)));
    }

    /**
     * generate an input
     *
     * @param   string  dot notation form for input - will match with Input handler and prefill if found
     * @param   string  label for form, pass false to not show label
     * @param   array   array of extra options for the input
     * 'wrap' => 'div' - wrapper element - pass false to not wrap
     * 'wrap_class' => 'form-group' - wrapper element default class (bootstrap defaults)
     * 'before' => '' - allows html to be entered before the label
     * 'between' => '' - allows html to be etnered between the label and the input
     * 'after' => '' - allows html to be entered after the input
     * 'label' => array() - allows extra attributes to be passed to label ('for' is generated automatically)
     * 'type' => 'input' - set the type of the input / select / textarea / checkbox / radio / file / hidden / password
     * 'value' => '' - Will be taken from Posts::get('fieldname') if exists.
     *               - If checkbox type, default value will be (int) 1. If value found in Post::get(),
     *                 will be compared and 'checked' set if necessary (unless manually set)
     *               - If select type, value will be used to set the selected option (unless manually set)
     * 'class' => 'form-control' - default class for form element (bootstrap defaults)
     * 'options' => array() - options for select box / radio buttons / checkboxs
     * 'hidden_checkbox' => true - For single checkboxes, if true a hidden field will be set as a 'fallback' if
     *                           - main checkbox not checked.
     * All other params will be compiled into attributes
     * @return  string  form element
     */
    public function input($field_name, $label, $params = array())
    {
        $input = array();

        // merge the default params with passed options
        if (isset($params['type']) && isset($this->input_defaults[$params['type']])) {
            $defaults = $this->input_defaults[$params['type']];
        } else {
            $defaults = $this->input_defaults['text'];
        }
        $params = array_merge($defaults, $params);

        // set the name field
        $params['name'] = $this->transformName($field_name);

        // setup the wrapper element if set
        if (! empty($params['wrap'])) {
            $input['0'] = '<' . $params['wrap'];

            if (! empty($params['wrap_class'])) {
                $input['0'] .= ' class="' . $params['wrap_class'] . '"';
            }
            $input['0'] .= '>';

            $input['100'] = '</' . $params['wrap'] . '>';
        }

        // check for 'before'
        if (! empty($params['before'])) {
            $input['5'] = $params['before'];
        }

        // check for 'after'
        if (! empty($params['after'])) {
            $input['95'] = $params['after'];
        }

        // check id
        if (empty($params['id'])) {
            $params['id'] = Str::camel(str_replace('.', '_', $field_name));
        }

        // add the 'for' for the label if we need a label
        if (! empty($label)) {
            $params['label']['for'] = $params['id'];
        }

        if ($params['type'] == 'checkbox' || $params['type'] == 'radio') {
            // build checkbox / radio

            // check for value in Input handler
            $input_value = \Whsuite\Inputs\Post::get($field_name);
            if (! empty($input_value)) {
                $params['checked'] = $input_value;
            }

            $input['50'] = $this->buildCheckbox($field_name, $label, $params);
        } else {
            // check for value in Input handler

            $input_value = \Whsuite\Inputs\Post::get($field_name);
            if (! empty($input_value) && ! isset($params['value'])) {
                $params['value'] = $input_value;
            }

            // build the label
            if (! empty($label) && $params['type'] != 'hidden') {
                $input['30'] = $this->label($label, $params['label']);
            }

            // check for 'between'
            if (! empty($params['between'])) {
                $input['40'] = $params['between'];
            }

            // build input
            if ($params['type'] == 'textarea' && isset($params['wysiwyg'])) {
                $func = 'buildWysiwyg';
            } else {
                $func = 'build' . ucwords($params['type']);
            }

            if (method_exists($this, $func)) {
                $input['50'] = $this->$func($field_name, $params);
            } else {
                $input['50'] = $this->buildInput($field_name, $params);
            }
        }

        ksort($input);
        return implode('', $input);
    }

    /**
     * generate a label
     *
     * @param   string  label text
     * @param   array   label attributes
     * @return  string  label string
     */
    public function label($text, $params)
    {
        $attributes = $this->compileAttributes($params);

        return '<label ' . $attributes . '>' . $text . '</label>';
    }

    /**
     * build a submit button
     *
     * @param   string  field name
     * @param   array   array of attributes
     * @return  string  form element
     */
    protected function buildSubmit($field_name, $params)
    {
        return $this->buildButton($field_name, $params);
    }

    /**
     * build a button
     *
     * @param   string  field name
     * @param   array   array of attributes
     * @return  string  form element
     */
    protected function buildButton($field_name, $params)
    {
        $label = (isset($params['value'])) ? $params['value'] : false;
        unset($params['value']);

        $attributes = $this->removeUnneededParams($params);
        $attributes = $this->compileAttributes($attributes);

        $button = '<button ' . $attributes . '>';

        if (isset($label) && ! empty($label)) {
            $button .= $label;
        }

        $button .= '</button>';

        return $button;
    }

    /**
     * build a select box
     *
     * @param   string  field name
     * @param   array   array of attributes
     * @return  string  form element
     */
    protected function buildSelect($field_name, $params)
    {
        $attributes = $this->removeUnneededParams($params);
        $attributes = $this->compileAttributes($attributes);

        $select = '<select ' . $attributes . '>';

        if (! empty($params['options'])) {
            foreach ($params['options'] as $value => $text) {
                if (is_array($text)) {
                    $select .= '<optgroup label="' . $value . '">';

                    foreach ($text as $child_value => $child_text) {
                        $option_attr = array(
                            'value' => $child_value
                        );

                        if (! empty($params['value']) && $child_value == $params['value']) {
                            $option_attr['selected'] = 'selected';
                        }
                        $option_attr = $this->compileAttributes($option_attr);

                        $select .= '<option ' . $option_attr . '>' . $child_text . '</option>';
                    }

                    $select .= '</optgroup>';
                } else {
                    $option_attr = array(
                        'value' => $value
                    );

                    if (! empty($params['value']) && $value == $params['value']) {
                        $option_attr['selected'] = 'selected';
                    }
                    $option_attr = $this->compileAttributes($option_attr);

                    $select .= '<option ' . $option_attr . '>' . $text . '</option>';
                }
            }
        }

        $select .= '</select>';

        return $select;
    }

    /**
     * build an input
     *
     * @param   string  field name
     * @param   array  array of attributes
     * @return  string  form element
     */
    protected function buildInput($field_name, $params)
    {
        $attributes = $this->removeUnneededParams($params);
        $attributes = $this->compileAttributes($attributes);

        return '<input ' . $attributes . '>';
    }

    /**
     * build a textarea
     *
     * @param   string  field name
     * @param   array   array of attributes
     * @return  string  form element
     */
    protected function buildTextarea($field_name, $params)
    {
        $textarea = '';

        $attributes = $this->removeUnneededParams($params);
        unset($attributes['type'], $attributes['value']);

        $id = $attributes['id'];

        $attributes = $this->compileAttributes($attributes);


        $textarea .= '<textarea ' . $attributes . '>';

        if (isset($params['value'])) {
            $textarea .= $params['value'];
        }

        $textarea .= '</textarea>';

        return $textarea;
    }

    /**
     * build a wysiwyg
     *
     * @param   string  field name
     * @param   array   array of attributes
     * @return  string  form element
     */
    protected function buildWysiwyg($field_name, $params)
    {
        $textarea = '';

        $attributes = $this->removeUnneededParams($params);
        unset($attributes['type'], $attributes['value']);

        $id = $attributes['id'];

        $attributes = $this->compileAttributes($attributes);
        if (! isset($params['form-type'])) {
            $params['form-type'] = 'form-horizontal';
        }

        if ($params['form-type'] == 'form-horizontal') {
            $textarea .= '<div class="col-md-9 col-sm-12 col-offset-md-3 nomargin nopadding">';
        } else {
            $textarea .= '';
        }

        $textarea .= '<textarea ' . $attributes . '>';

        if (isset($params['value'])) {
            $textarea .= $params['value'];
        }

        $textarea .= '</textarea>';
        $textarea .= '<script>CKEDITOR.replace("'.$id.'");</script>';
        unset($params['wysiwyg']);

        if ($params['form-type'] == 'form-horizontal') {
            $textarea .= '</div>';
        } else {
            $textarea .= '';
        }

        return $textarea;
    }

    /**
     * build a checkbox / radio button
     *
     * @param   string  field name
     * @param   string  label
     * @param   array   array of params
     * @return  string
     */
    protected function buildCheckbox($field_name, $label, $params)
    {
        $block = '';

        // get the label params
        $label_params = $params['label'];

        // get rid of all the unneeded elements
        $attributes = $this->removeUnneededParams($params);

        // check for hidden checkbox
        if (! empty($params['hidden_checkbox']) && $params['hidden_checkbox'] === true) {
            $hidden_params = array(
                'type' => 'hidden',
                'value' => '0',
                'wrap' => false,
                'id' => '_' . $params['id']
            );
            $hidden_params = array_merge($attributes, $hidden_params);
            $block .= $this->hidden($field_name, $hidden_params);
        }

        // check for value
        if (! empty($params['checked']) && $params['checked'] == $params['value']) {
            $params['checked'] = 'checked';
        }

        // compile attributes and build input
        $attributes = $this->compileAttributes($attributes);

        $input = '<input ' . $attributes . '>';

        // check for a between element
        if (! empty($params['between'])) {
            $input .= $params['between'];
        }

        $block .= $input . $this->label($label, $label_params);

        return $block;
    }

    /**
     * compare params array against ignore list and remove unneeded params
     *
     * @param   array   array of params
     * @return  array   array of params to be passed to compileAttributes
     */
    protected function removeUnneededParams($params)
    {
        foreach ($params as $key => $value) {
            if (isset($this->param_remove_list[$key])) {
                unset($params[$key]);
            }
        }

        return $params;
    }

    /**
     * compile an array of key=>value params into key="value" attributes for form elements
     *
     * @param   array   array of parameters
     * @return  string  string containing the attributes for the form element
     */
    protected function compileAttributes(array $params)
    {
        $str = '';

        foreach ($params as $key => $value) {
            if (in_array($key, $this->param_attr_exceptions)) {
                $str .= $key . ' ';
            } else {
                $str .= $key . '="' . $value . '" ';
            }
        }

        return trim($str);
    }

    /**
     * transform dot notation string into form name
     *
     * @param   string  dot notation format of the input name
     * @return  string  standard array format for input name
     */
    protected function transformName($name)
    {
        $transformed = '';

        if (strpos($name, '.') !== false) {
            $bits = explode('.', $name);

            $transformed = $bits['0'];
            unset($bits['0']);

            if (count($bits) > 0) {
                foreach ($bits as $bit) {
                    $transformed .= '[' . $bit . ']';
                }
            }

            return $transformed;
        } else {
            return $name;
        }
    }
}
