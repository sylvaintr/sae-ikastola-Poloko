<?php

const INVALID_SELECTED_ATTRIBUTE_EUS = 'Hautatutako :attribute ez da baliozkoa.';

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => ':attribute onartu behar da.',
    'accepted_if' => ':attribute onartu behar da :other :value denean.',
    'active_url' => ':attribute ez da URL baliozkoa.',
    'after' => ':attribute :date baino geroagoko data izan behar da.',
    'after_or_equal' => ':attribute :date baino beranduagoko edo berdineko data izan behar da.',
    'alpha' => ':attribute letrak bakarrik izan behar ditu.',
    'alpha_dash' => ':attribute letrak, zenbakiak, gidoiak eta azpimarrak bakarrik izan behar ditu.',
    'alpha_num' => ':attribute letrak eta zenbakiak bakarrik izan behar ditu.',
    'array' => ':attribute array bat izan behar da.',
    'ascii' => ':attribute karaktere alfanumeriko eta sinbolo bakar-bit bat baino ezin ditu izan.',
    'before' => ':attribute :date baino lehenagoko data izan behar da.',
    'before_or_equal' => ':attribute :date baino lehenagoko edo berdineko data izan behar da.',
    'between' => [
        'array' => ':attribute :min eta :max elementu artean izan behar ditu.',
        'file' => ':attribute :min eta :max kilobyte artean izan behar du.',
        'numeric' => ':attribute :min eta :max artean izan behar da.',
        'string' => ':attribute :min eta :max karaktere artean izan behar ditu.',
    ],
    'boolean' => ':attribute eremuak egia edo gezurra izan behar du.',
    'can' => ':attribute eremuak balio baimendu gabeko balioa du.',
    'confirmed' => ':attribute berrespena ez dator bat.',
    'contains' => ':attribute eremuak ez du balio baliogarri hautaturik.',
    'current_password' => 'Pasahitza okerra da.',
    'date' => ':attribute ez da data baliozkoa.',
    'date_equals' => ':attribute :date berdina izan behar da.',
    'date_format' => ':attribute ez dator bat :format formatuarekin.',
    'decimal' => ':attribute eremuak :decimal hamartarrak izan behar ditu.',
    'declined' => ':attribute eremua ukatu behar da.',
    'declined_if' => ':attribute eremua ukatu behar da :other :value denean.',
    'different' => ':attribute eta :other desberdinak izan behar dira.',
    'digits' => ':attribute :digits digitu izan behar ditu.',
    'digits_between' => ':attribute :min eta :max digitu artean izan behar ditu.',
    'dimensions' => ':attribute irudiaren dimentsioak ez dira baliozkoak.',
    'distinct' => ':attribute eremuak bikoiztutako balioa du.',
    'doesnt_end_with' => ':attribute ez da honela amaitzen behar: :values.',
    'doesnt_start_with' => ':attribute ez da honela hasi behar: :values.',
    'email' => ':attribute helbide elektroniko baliozkoa izan behar du.',
    'ends_with' => ':attribute honela amaitzen behar da: :values.',
    'enum' => INVALID_SELECTED_ATTRIBUTE_EUS,
    'exists' => INVALID_SELECTED_ATTRIBUTE_EUS,
    'extensions' => ':attribute eremuak ondorengo luzapenetako bat izan behar du: :values.',
    'file' => ':attribute fitxategi bat izan behar du.',
    'filled' => ':attribute eremuak balioa izan behar du.',
    'gt' => [
        'array' => ':attribute eremuak :value elementu baino gehiago izan behar ditu.',
        'file' => ':attribute eremuak :value kilobyte baino handiagoa izan behar du.',
        'numeric' => ':attribute eremuak :value baino handiagoa izan behar du.',
        'string' => ':attribute eremuak :value karaktere baino gehiago izan behar ditu.',
    ],
    'gte' => [
        'array' => ':attribute eremuak :value elementu edo gehiago izan behar ditu.',
        'file' => ':attribute eremuak :value kilobyte edo gehiago izan behar du.',
        'numeric' => ':attribute eremuak :value baino handiagoa edo berdina izan behar du.',
        'string' => ':attribute eremuak gutxienez :value karaktere izan behar ditu.',
    ],
    'hex_color' => ':attribute kolore hexadezimal baliozkoa izan behar du.',
    'image' => ':attribute irudi bat izan behar du.',
    'in' => INVALID_SELECTED_ATTRIBUTE_EUS,
    'in_array' => ':attribute eremua :other-en existitzen behar da.',
    'integer' => ':attribute zenbaki osoa izan behar du.',
    'ip' => ':attribute IP helbide baliozkoa izan behar du.',
    'ipv4' => ':attribute IPv4 helbide baliozkoa izan behar du.',
    'ipv6' => ':attribute IPv6 helbide baliozkoa izan behar du.',
    'json' => ':attribute JSON katea baliozkoa izan behar du.',
    'list' => ':attribute zerrenda bat izan behar du.',
    'lowercase' => ':attribute minuskulaz idatzita egon behar du.',
    'lt' => [
        'array' => ':attribute eremuak :value elementu baino gutxiago izan behar ditu.',
        'file' => ':attribute eremuak :value kilobyte baino txikiagoa izan behar du.',
        'numeric' => ':attribute eremuak :value baino txikiagoa izan behar du.',
        'string' => ':attribute eremuak :value karaktere baino gutxiago izan behar ditu.',
    ],
    'lte' => [
        'array' => ':attribute eremuak :value elementu edo gutxiago izan behar ditu.',
        'file' => ':attribute eremuak :value kilobyte edo gutxiago izan behar du.',
        'numeric' => ':attribute eremuak :value baino txikiagoa edo berdina izan behar du.',
        'string' => ':attribute eremuak gehienez :value karaktere izan behar ditu.',
    ],
    'mac_address' => ':attribute MAC helbide baliozkoa izan behar du.',
    'max' => [
        'array' => ':attribute eremuak ezin du :max elementu baino gehiago izan.',
        'file' => ':attribute eremuak ezin du :max kilobyte baino handiagoa izan.',
        'numeric' => ':attribute eremuak ezin du :max baino handiagoa izan.',
        'string' => ':attribute eremuak ezin du :max karaktere baino gehiago izan.',
    ],
    'max_digits' => ':attribute eremuak ezin du :max digitu baino gehiago izan.',
    'mimes' => ':attribute motako fitxategia izan behar du: :values.',
    'mimetypes' => ':attribute motako fitxategia izan behar du: :values.',
    'min' => [
        'array' => ':attribute eremuak gutxienez :min elementu izan behar ditu.',
        'file' => ':attribute eremuak gutxienez :min kilobyte izan behar du.',
        'numeric' => ':attribute eremuak gutxienez :min izan behar du.',
        'string' => ':attribute eremuak gutxienez :min karaktere izan behar ditu.',
    ],
    'min_digits' => ':attribute eremuak gutxienez :min digitu izan behar ditu.',
    'missing' => ':attribute eremua falta behar da.',
    'missing_if' => ':attribute eremua falta behar da :other :value denean.',
    'missing_unless' => ':attribute eremua falta behar da :other :value ez bada.',
    'missing_with' => ':attribute eremua falta behar da :values presente dagoenean.',
    'missing_with_all' => ':attribute eremua falta behar da :values presente daudenean.',
    'multiple_of' => ':attribute :value-ren multiploa izan behar du.',
    'not_in' => INVALID_SELECTED_ATTRIBUTE_EUS,
    'not_regex' => ':attribute formatua ez da baliozkoa.',
    'numeric' => ':attribute zenbakia izan behar du.',
    'password' => [
        'letters' => ':attribute eremuak gutxienez letra bat izan behar du.',
        'mixed' => ':attribute eremuak gutxienez letra larri bat eta letra xehe bat izan behar ditu.',
        'numbers' => ':attribute eremuak gutxienez zenbaki bat izan behar du.',
        'symbols' => ':attribute eremuak gutxienez karaktere berezi bat izan behar du.',
        'uncompromised' => 'Emandako :attribute datu-ihes batean agertu da. Mesedez, hautatu beste :attribute bat.',
    ],
    'present' => ':attribute eremua presente egon behar du.',
    'present_if' => ':attribute eremua presente egon behar du :other :value denean.',
    'present_unless' => ':attribute eremua presente egon behar du :other :value ez bada.',
    'present_with' => ':attribute eremua presente egon behar du :values presente dagoenean.',
    'present_with_all' => ':attribute eremua presente egon behar du :values presente daudenean.',
    'prohibited' => ':attribute eremua debekatuta dago.',
    'prohibited_if' => ':attribute eremua debekatuta dago :other :value denean.',
    'prohibited_unless' => ':attribute eremua debekatuta dago :other :values-en ez badago.',
    'prohibits' => ':attribute eremuak :other presente izatea debekatzen du.',
    'regex' => ':attribute formatua ez da baliozkoa.',
    'required' => ':attribute eremua beharrezkoa da.',
    'required_array_keys' => ':attribute eremuak :values-rako sarrerak izan behar ditu.',
    'required_if' => ':attribute eremua beharrezkoa da :other :value denean.',
    'required_if_accepted' => ':attribute eremua beharrezkoa da :other onartzen denean.',
    'required_if_declined' => ':attribute eremua beharrezkoa da :other ukatzen denean.',
    'required_unless' => ':attribute eremua beharrezkoa da :other :values-en ez badago.',
    'required_with' => ':attribute eremua beharrezkoa da :values presente dagoenean.',
    'required_with_all' => ':attribute eremua beharrezkoa da :values presente daudenean.',
    'required_without' => ':attribute eremua beharrezkoa da :values ez dagoenean.',
    'required_without_all' => ':attribute eremua beharrezkoa da :values inor ez dagoenean.',
    'same' => ':attribute eta :other bat etorri behar dira.',
    'size' => [
        'array' => ':attribute eremuak :size elementu izan behar ditu.',
        'file' => ':attribute eremuak :size kilobyte izan behar du.',
        'numeric' => ':attribute :size izan behar du.',
        'string' => ':attribute eremuak :size karaktere izan behar ditu.',
    ],
    'starts_with' => ':attribute honela hasi behar da: :values.',
    'string' => ':attribute katea izan behar du.',
    'timezone' => ':attribute ordutegia baliozkoa izan behar du.',
    'unique' => ':attribute jadanik hartuta dago.',
    'uploaded' => ':attribute ezin izan da igo.',
    'uppercase' => ':attribute maiuskulaz idatzita egon behar du.',
    'url' => ':attribute URL baliozkoa izan behar du.',
    'ulid' => ':attribute ULID baliozkoa izan behar du.',
    'uuid' => ':attribute UUID baliozkoa izan behar du.',


    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "rule.attribute" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'email' => [
            'unique' => 'Kontu bat dago dagoeneko email honekin.',
        ],
        'password' => [
            'confirmed' => 'Bi pasahitzak ez datoz bat.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        'email' => 'helbide elektronikoa',
        'password' => 'pasahitza',
        'password_confirmation' => 'pasahitzaren berrespena',
        'prenom' => 'izena',
        'nom' => 'abizena',
        'languePref' => 'hizkuntza hobetsia',
        'g-recaptcha-response' => 'captcha',
    ],

];

