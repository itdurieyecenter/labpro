<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('asset_url')) {
    function asset_url($path = '') {
        $ci =& get_instance();
        return $ci->config->base_url('assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('aset_url')) {
    function aset_url($path = '') {
        $ci =& get_instance();
        return $ci->config->base_url('asets/' . ltrim($path, '/'));
    }
}

if (!function_exists('ui_icon')) {
    function ui_icon($name, $size = 'md', $class = '') {
        if (empty($name)) {
            return '';
        }

        $sizeMap = [
            'xs' => 'lis-icon-xs',
            'sm' => 'lis-icon-sm',
            'md' => 'lis-icon-md',
            'lg' => 'lis-icon-lg',
            'xl' => 'lis-icon-xl',
        ];

        $iconClass = 'lis-icon';
        if (isset($sizeMap[$size])) {
            $iconClass .= ' ' . $sizeMap[$size];
        } elseif (is_numeric($size)) {
            $iconClass .= ' lis-icon-md'; // fallback ke md untuk ukuran numeric
        }

        $iconClass = $iconClass . ($class ? ' ' . $class : '');

        return '<i data-lucide="' . html_escape($name) . '" class="' . html_escape($iconClass) . '"></i>';
    }
}

if (!function_exists('ui_btn')) {
    function ui_btn($text, $variant = 'primary', $size = 'md', $attr = []) {
        $baseClass = 'lis-btn lis-btn-' . $size . ' lis-btn-' . $variant;
        $class = isset($attr['class']) ? $baseClass . ' ' . $attr['class'] : $baseClass;
        unset($attr['class']);

        $icon = '';
        if (!empty($attr['icon'])) {
            $iconSize = $size === 'sm' ? 'sm' : ($size === 'lg' ? 'lg' : 'md');
            $icon = ui_icon($attr['icon'], $iconSize);
            unset($attr['icon']);
        }

        $tag = isset($attr['href']) ? 'a' : 'button';
        if ($tag === 'button' && !isset($attr['type'])) {
            $attr['type'] = 'button';
        }

        $attrString = '';
        foreach ($attr as $key => $val) {
            $attrString .= ' ' . html_escape($key) . '="' . html_escape($val) . '"';
        }

        return '<' . $tag . ' class="' . html_escape($class) . '"' . $attrString . '>' . $icon . '<span>' . $text . '</span></' . $tag . '>';
    }
}

if (!function_exists('ui_panel_start')) {
    function ui_panel_start($title = '', $icon = '', $actions = '', $attr = []) {
        $class = isset($attr['class']) ? 'lis-panel ' . $attr['class'] : 'lis-panel';
        unset($attr['class']);

        $attrString = '';
        foreach ($attr as $key => $val) {
            $attrString .= ' ' . html_escape($key) . '="' . html_escape($val) . '"';
        }

        $html = '<div class="' . html_escape($class) . '"' . $attrString . '>';
        if (!empty($title) || !empty($actions)) {
            $html .= '<div class="lis-panel-header">';
            $html .= '<div class="lis-panel-title">';
            if (!empty($icon)) {
                $html .= '<span class="text-sky-600">' . ui_icon($icon, 20) . '</span>';
            }
            $html .= '<span>' . html_escape($title) . '</span>';
            $html .= '</div>';
            if (!empty($actions)) {
                $html .= '<div class="flex items-center gap-2">' . $actions . '</div>';
            }
            $html .= '</div>';
        }
        $html .= '<div class="lis-panel-body">';
        return $html;
    }
}

if (!function_exists('ui_panel_end')) {
    function ui_panel_end($footer = '') {
        $html = '</div>';
        if (!empty($footer)) {
            $html .= '<div class="lis-panel-footer">' . $footer . '</div>';
        }
        $html .= '</div>';
        return $html;
    }
}

if (!function_exists('ui_input')) {
    function ui_input($name, $label = '', $value = '', $type = 'text', $attr = []) {
        $hasError = !empty($attr['error']);
        $errorMsg = $hasError ? $attr['error'] : '';
        unset($attr['error']);

        $inputClass = 'lis-input' . ($hasError ? ' lis-input-error' : '');
        if (isset($attr['class'])) {
            $inputClass .= ' ' . $attr['class'];
            unset($attr['class']);
        }

        $id = isset($attr['id']) ? $attr['id'] : 'input_' . $name;
        $attr['id'] = $id;

        $iconHtml = '';
        $paddingClass = '';
        if (!empty($attr['icon'])) {
            $iconHtml = '<div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">' . ui_icon($attr['icon'], 18) . '</div>';
            $paddingClass = ' pl-10';
            unset($attr['icon']);
        }

        $helpText = '';
        if (!empty($attr['help'])) {
            $helpText = '<p class="lis-help">' . html_escape($attr['help']) . '</p>';
            unset($attr['help']);
        }

        $attrString = '';
        foreach ($attr as $key => $val) {
            $attrString .= ' ' . html_escape($key) . '="' . html_escape($val) . '"';
        }

        $html = '<div class="w-full mb-4">';
        if (!empty($label)) {
            $html .= '<label for="' . html_escape($id) . '" class="lis-label">' . html_escape($label) . '</label>';
        }
        $html .= '<div class="relative">';
        if (!empty($iconHtml)) {
            $html .= $iconHtml;
        }
        $html .= '<input type="' . html_escape($type) . '" name="' . html_escape($name) . '" value="' . html_escape($value) . '" class="' . html_escape($inputClass . $paddingClass) . '"' . $attrString . '>';
        $html .= '</div>';
        if (!empty($helpText)) {
            $html .= $helpText;
        }
        if ($hasError) {
            $html .= '<p class="lis-error">' . ui_icon('alert-circle', 14) . ' ' . html_escape($errorMsg) . '</p>';
        }
        $html .= '</div>';
        return $html;
    }
}

if (!function_exists('ui_select')) {
    function ui_select($name, $label = '', $options = [], $selected = '', $attr = []) {
        $hasError = !empty($attr['error']);
        $errorMsg = $hasError ? $attr['error'] : '';
        unset($attr['error']);

        $selectClass = 'lis-input' . ($hasError ? ' lis-input-error' : '');
        if (isset($attr['class'])) {
            $selectClass .= ' ' . $attr['class'];
            unset($attr['class']);
        }

        $id = isset($attr['id']) ? $attr['id'] : 'select_' . $name;
        $attr['id'] = $id;

        $helpText = '';
        if (!empty($attr['help'])) {
            $helpText = '<p class="lis-help">' . html_escape($attr['help']) . '</p>';
            unset($attr['help']);
        }

        $attrString = '';
        foreach ($attr as $key => $val) {
            $attrString .= ' ' . html_escape($key) . '="' . html_escape($val) . '"';
        }

        $html = '<div class="w-full mb-4">';
        if (!empty($label)) {
            $html .= '<label for="' . html_escape($id) . '" class="lis-label">' . html_escape($label) . '</label>';
        }
        $html .= '<select name="' . html_escape($name) . '" class="' . html_escape($selectClass) . '"' . $attrString . '>';
        foreach ($options as $val => $optText) {
            $isSelected = ((string)$val === (string)$selected) ? ' selected' : '';
            $html .= '<option value="' . html_escape($val) . '"' . $isSelected . '>' . html_escape($optText) . '</option>';
        }
        $html .= '</select>';
        if (!empty($helpText)) {
            $html .= $helpText;
        }
        if ($hasError) {
            $html .= '<p class="lis-error">' . ui_icon('alert-circle', 14) . ' ' . html_escape($errorMsg) . '</p>';
        }
        $html .= '</div>';
        return $html;
    }
}

if (!function_exists('ui_badge')) {
    function ui_badge($text, $variant = 'primary', $icon = '') {
        $iconHtml = !empty($icon) ? ui_icon($icon, 14, 'mr-1') : '';
        return '<span class="lis-badge lis-badge-' . html_escape($variant) . '">' . $iconHtml . html_escape($text) . '</span>';
    }
}

if (!function_exists('ui_alert')) {
    function ui_alert($message, $variant = 'info', $dismissible = true) {
        $variants = [
            'success' => ['bg' => 'bg-emerald-50 border-emerald-200 text-emerald-800', 'icon' => 'check-circle-2', 'iconColor' => 'text-emerald-500'],
            'danger' => ['bg' => 'bg-rose-50 border-rose-200 text-rose-800', 'icon' => 'alert-triangle', 'iconColor' => 'text-rose-500'],
            'warning' => ['bg' => 'bg-amber-50 border-amber-200 text-amber-800', 'icon' => 'alert-circle', 'iconColor' => 'text-amber-500'],
            'info' => ['bg' => 'bg-sky-50 border-sky-200 text-sky-800', 'icon' => 'info', 'iconColor' => 'text-sky-500'],
        ];

        $cfg = isset($variants[$variant]) ? $variants[$variant] : $variants['info'];

        $html = '<div class="flex items-start gap-3 p-4 rounded-xl border ' . $cfg['bg'] . ' mb-4" role="alert" x-data="{ open: true }" x-show="open">';
        $html .= '<span class="' . $cfg['iconColor'] . ' flex-shrink-0 mt-0.5">' . ui_icon($cfg['icon'], 18) . '</span>';
        $html .= '<div class="text-sm flex-1 leading-relaxed">' . $message . '</div>';
        if ($dismissible) {
            $html .= '<button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600 transition-colors p-0.5 rounded-lg">';
            $html .= ui_icon('x', 16);
            $html .= '</button>';
        }
        $html .= '</div>';
        return $html;
    }
}

if (!function_exists('get_setting')) {
    function get_setting($key, $default = '', $refresh = false) {
        static $settings_cache = null;
        $ci =& get_instance();
        
        if ($settings_cache === null || $refresh) {
            $settings_cache = [];
            if ($ci->db->table_exists('pengaturan')) {
                $rows = $ci->db->select('setting_key, setting_value')->get('pengaturan')->result_array();
                foreach ($rows as $r) {
                    $settings_cache[$r['setting_key']] = $r['setting_value'];
                }
            }
        }
        
        return array_key_exists($key, $settings_cache) ? $settings_cache[$key] : $default;
    }
}

if (!function_exists('app_logo_url')) {
    function app_logo_url($type = 'instansi') {
        if ($type === 'instansi') {
            $path = get_setting('logo_instansi', '');
            if (!empty($path) && file_exists(FCPATH . $path)) {
                return base_url($path);
            }
            return base_url('assets/images/logo_labkes_default.svg');
        } elseif ($type === 'kan') {
            $path = get_setting('logo_kan', '');
            if (!empty($path) && file_exists(FCPATH . $path)) {
                return base_url($path);
            }
            return base_url('assets/images/logo_kan_default.svg');
        }
        return base_url('assets/images/logo_labkes_default.svg');
    }
}

if (!function_exists('kop_surat_data')) {
    function kop_surat_data() {
        $logo_instansi_path = get_setting('logo_instansi', '');
        $has_custom_instansi = !empty($logo_instansi_path) && file_exists(FCPATH . $logo_instansi_path);
        
        $logo_kan_path = get_setting('logo_kan', '');
        $has_custom_kan = !empty($logo_kan_path) && file_exists(FCPATH . $logo_kan_path);

        return [
            'logo_instansi_url'       => app_logo_url('instansi'),
            'logo_kan_url'            => app_logo_url('kan'),
            'has_custom_instansi'     => $has_custom_instansi,
            'has_custom_kan'          => $has_custom_kan,
            'logo_instansi_path'      => $logo_instansi_path,
            'logo_kan_path'           => $logo_kan_path,
            'line1'                   => get_setting('kop_header_line1', 'PEMERINTAH PROVINSI RIAU'),
            'line2'                   => get_setting('kop_header_line2', 'DINAS KESEHATAN'),
            'line3'                   => get_setting('kop_header_line3', 'UPT LABORATORIUM KESEHATAN DAN LINGKUNGAN'),
            'instansi'                => get_setting('nama_instansi', 'UPT Laboratorium Kesehatan & Lingkungan Provinsi Riau'),
            'induk'                   => get_setting('instansi_induk', 'Dinas Kesehatan Provinsi Riau'),
            'alamat'                  => get_setting('alamat_lengkap', 'Jl. Cut Nyak Dien No. 01, Kel. Sukajadi'),
            'kota'                    => get_setting('kota_kabupaten', 'Kota Pekanbaru'),
            'provinsi'                => get_setting('provinsi', 'Riau'),
            'kode_pos'                => get_setting('kode_pos', '28121'),
            'telepon'                 => get_setting('telepon', '(0761) 23456'),
            'whatsapp'                => get_setting('whatsapp_layanan', '0812-7567-8901'),
            'email'                   => get_setting('email_resmi', 'labkes@riau.go.id'),
            'website'                 => get_setting('website_resmi', 'https://labkesda.riau.go.id'),
            'nomor_izin'              => get_setting('nomor_izin_lab', '445/DINKES-LAB/2024/008'),
            'footer_note'             => get_setting('kop_footer_note', 'Hasil pemeriksaan ini sah dicetak otomatis oleh LIS PRO dan terakreditasi KAN ISO/IEC 17025'),
            'kepala_lab'              => get_setting('kepala_lab', 'dr. H. Hendra Saputra, Sp.PK'),
            'nip_kepala'              => get_setting('nip_kepala_lab', '19780512 200501 1 004'),
            'pangkat_kepala'          => get_setting('pangkat_kepala_lab', 'Pembina (Gol. IV/a)'),
            'dokter_penanggung_jawab' => get_setting('dokter_penanggung_jawab', 'dr. Hj. Siti Rahmah, Sp.PK, M.Kes'),
            'sip_dokter'              => get_setting('sip_dokter', '503/SIP-D/2023/0421'),
        ];
    }
}

if (!function_exists('render_kop_surat')) {
    function render_kop_surat($options = [], $return = true) {
        $ci =& get_instance();
        return $ci->load->view('layout/kop_surat', $options, $return);
    }
}

if (!function_exists('app_profile')) {
    function app_profile() {
        return [
            'name'       => get_setting('nama_aplikasi', 'LIS PRO - Laboratory Information System'),
            'brand'      => get_setting('singkatan_aplikasi', 'LIS PRO LABKES RIAU'),
            'instansi'   => get_setting('nama_instansi', 'UPT Laboratorium Kesehatan & Lingkungan Provinsi Riau'),
            'induk'      => get_setting('instansi_induk', 'Dinas Kesehatan Provinsi Riau'),
            'slogan'     => get_setting('slogan_aplikasi', 'Akurat, Cepat, Terpercaya, dan Terstandar Mutu Nasional'),
            'alamat'     => get_setting('alamat_lengkap', 'Jl. Cut Nyak Dien No. 01, Kel. Sukajadi'),
            'kota'       => get_setting('kota_kabupaten', 'Kota Pekanbaru'),
            'provinsi'   => get_setting('provinsi', 'Riau'),
            'telepon'    => get_setting('telepon', '(0761) 23456'),
            'whatsapp'   => get_setting('whatsapp_layanan', '0812-7567-8901'),
            'email'      => get_setting('email_resmi', 'labkes@riau.go.id'),
            'website'    => get_setting('website_resmi', 'https://labkesda.riau.go.id'),
            'kepala_lab' => get_setting('kepala_lab', 'dr. H. Hendra Saputra, Sp.PK'),
            'nip_kepala' => get_setting('nip_kepala_lab', '19780512 200501 1 004'),
            'logo'       => app_logo_url('instansi'),
            'logo_kan'   => app_logo_url('kan'),
        ];
    }
}


