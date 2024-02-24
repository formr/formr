<?php

trait Tailwind
{
    # Wrapper for Tailwind CSS
    # https://tailwindcss.com

    public static function tailwind_css($key = ''): string|array
    {
        $array = [
            'alert-e' => 'alert-e',
            'alert-i' => 'alert-i',
            'alert-s' => 'alert-s',
            'alert-w' => 'alert-w',
            'button' => 'button',
            'button-danger' => 'button-danger',
            'button-primary' => 'button-primary',
            'button-secondary' => 'button-secondary',
            'checkbox' => 'checkbox',
            'div' => 'div',
            'file' => 'file',
            'file-label' => 'file-label',
            'help' => 'help',
            'is-invalid' => 'is-invalid',
            'is-valid' => 'is-valid',
            'input' => 'input',
            'label' => 'label',
            'radio' => 'radio',
            'textarea' => 'textarea',
            'text-error' => 'text-error',
            'text-success' => 'text-success',
            'message-header' => 'message-header',
            'message-body' => 'message-body',
        ];

        /*
         * Tailwind Classes
         * Below are the classes used to create this wrapper
         * Just drop them into your CSS file, or add them to your tailwind config
         *
         @tailwind base;
         @tailwind components;
         @tailwind utilities;
         @layer base {
            .alert-e {@apply p-4 rounded-md bg-red-50 text-red-500 shadow border border-red-100;}
            .alert-i {@apply p-4 rounded-md bg-blue-50 text-blue-500 shadow border border-blue-100;}
            .alert-s {@apply p-4 rounded-md bg-green-50 text-green-500 shadow border border-green-100;}
            .alert-w {@apply p-4 rounded-md bg-yellow-50 text-yellow-500 shadow border border-yellow-100;}
            .button {@apply rounded-md bg-slate-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-600;}
            .button-danger {@apply rounded-md bg-red-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600;}
            .button-primary {@apply rounded-md bg-slate-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-600;}
            .button-secondary {@apply rounded-md bg-gray-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-600;}
            .checkbox {@apply h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600;}
            .div {@apply py-3;}
            .file {@apply sr-only;}
            .file-label {@apply w-28 p-8 flex items-center justify-center cursor-pointer rounded-md bg-white px-3.5 py-2.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50;}
            .help {@apply text-slate-500 text-sm mt-1;}
            .is-invalid {@apply !text-red-500 !border !border-red-500;}
            .is-valid {@apply !text-green-500 !border !border-green-500;}
            .input {@apply mt-2 block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6;}
            .label {@apply text-slate-600 text-sm font-semibold;}
            .radio {@apply h-4 w-4 rounded-full border-gray-300 text-indigo-600 focus:ring-indigo-600;}
            .textarea {@apply block flex-1 border-0 bg-transparent py-1.5 pl-1 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6;}
            .text-error {@apply !text-red-500;}
            .text-success {@apply !text-green-500;}
            .message-header {@apply text-lg font-bold;}
            .message-body {@apply font-base;}
        }

        or you could just replace the array above with the classes
        $array = [
            'alert-e' => 'p-4 rounded-md bg-red-50 text-red-500 shadow border border-red-100',
            'alert-i' => 'p-4 rounded-md bg-blue-50 text-blue-500 shadow border border-blue-100',
            'alert-s' => 'p-4 rounded-md bg-green-50 text-green-500 shadow border border-green-100',
            'alert-w' => 'p-4 rounded-md bg-yellow-50 text-yellow-500 shadow border border-yellow-100',
            'button' => 'rounded-md bg-slate-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-600',
            'button-danger' => 'rounded-md bg-red-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600',
            'button-primary' => 'rounded-md bg-slate-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-600',
            'button-secondary' => 'rounded-md bg-gray-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-600',
            'checkbox' => 'h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600',
            'div' => 'py-3',
            'file' => 'sr-only',
            'file-label' => 'w-28 p-8 flex items-center justify-center cursor-pointer rounded-md bg-white px-3.5 py-2.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50',
            'help' => 'text-slate-500 text-sm mt-1',
            'is-invalid' => '!text-red-500 !border !border-red-500',
            'is-valid' => '!text-green-500 !border !border-green-500',
            'input' => 'mt-2 block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6',
            'label' => 'text-slate-600 text-sm font-semibold',
            'radio' => 'h-4 w-4 rounded-full border-gray-300 text-indigo-600 focus:ring-indigo-600',
            'textarea' => 'block flex-1 border-0 bg-transparent py-1.5 pl-1 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6',
            'text-error' => '!text-red-500',
            'text-success' => '!text-green-500',
            'message-header' => 'text-lg font-bold',
            'message-body' => 'font-base',
        ];
        */

        if ($key) {
            return $array[$key] ?? '';
        }

        return $array;
    }

    public function tailwind($element = '', $data = ''): string
    {
        if (empty($data)) {
            return '';
        }

        # define our $return variable with a new line
        $return = PHP_EOL;

        # add a comment if $form->comments is enabled
        $return .= $this->formr->_print_field_comment($data);

        # open the wrapper
        if ($this->formr->use_element_wrapper_div) {
            $return .= "<div id=\"{$this->formr->make_id($data)}\" class=\"".static::tailwind_css('div')."\">".PHP_EOL;
        }

        # create a file element. we're using a styled div to override the browser's file button
        if (isset($data['type']) && $data['type'] == 'file') {
            if ($this->formr->is_not_empty($data['label'])) {
                $return .= "<label class=\"".static::tailwind_css('file-label')."\" for=\"{$this->formr->make_id($data)}\">".PHP_EOL;
                $return .= $data['label'].PHP_EOL;
                $return .= $this->formr->insert_required_indicator($data);
                $return .= "<div class=\"".static::tailwind_css('file')."\">Choose File</div>".PHP_EOL;
                $return .= "</label>".PHP_EOL;
                $return .= $element.PHP_EOL;
            }
        } else {
            if ($this->formr->is_not_empty($data['label'])) {
                $return .= "<label class=\"".static::tailwind_css('label')."\" for=\"{$this->formr->make_id($data)}\">".PHP_EOL;
                if ($this->formr->type_is_checkbox($data)) {
                    # checkbox or radio
                    $return .= "<span class=\"mr-1\">{$element}</span>".PHP_EOL;
                    $return .= $data['label'].PHP_EOL;
                } else {
                    # everything else
                    $return .= $data['label'].PHP_EOL;
                    $return .= $element.PHP_EOL;
                }
                $return .= $this->formr->insert_required_indicator($data);
                $return .= "</label>".PHP_EOL;
            } else {
                # a label wasn't supplied; just return the element
                $return .= $element."&nbsp;".PHP_EOL;
            }
        }

        # show inline help or error message
        if (! empty($data['inline'])) {
            if ($this->formr->is_in_brackets($data['inline'])) {
                if ($this->formr->in_errors($data['name'])) {
                    $return .= "<p class=\"".static::tailwind_css('help')." ".static::tailwind_css('text-error')."\">".PHP_EOL;
                    $return .= trim($data['inline'], '[]').PHP_EOL;
                    $return .= "</p>".PHP_EOL;
                }
            } else {
                $return .= "<p class=\"".static::tailwind_css('help')."\">".PHP_EOL;
                $return .= $data['inline'].PHP_EOL;
                $return .= "</p>".PHP_EOL;
            }
        } else {
            if ($this->formr->submitted() && $this->formr->in_errors($data['name']) && $this->formr->inline_errors) {
                $return .= "<p class=\"".static::tailwind_css('help')." ".static::tailwind_css('text-error')."\">".PHP_EOL;
                $return .= $this->formr->errors[$data['name']].PHP_EOL;
                $return .= "</p>".PHP_EOL;
            }
        }

        # close the wrapper
        if ($this->formr->use_element_wrapper_div) {
            $return .= '</div>'.PHP_EOL;
        }

        return $return;
    }
}