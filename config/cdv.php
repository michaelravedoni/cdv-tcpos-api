<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Config
    |--------------------------------------------------------------------------
    |
    */

    'default_product_min_quantity' => 1,
    'default_product_max_quantity' => 6,
    'default_customer_id' => 897,
    'default_shop_id' => 1,
    'default_price_level_id' => 2,
    'default_image_type' => 'A',
    'default_confirm_order_operation' => 'T',

    'customer_min_cardum' => 10000,
    'customer_max_cardum' => 49000,

    // ID d'un produit "Frais de port" définit dans TCPOS, a utiliser pour traduire les frais de ports de la commande en... produit (#TCPOS-BestValue #CobolConvention1983)
    'tcpos_defaultShippingItemId' => 11334,

    // ID des attributs dans Woocommerce
    'wc_attribute_ids' => [
        'fillingLevelAttribute' => 7,
        'grapeAttribute' => 8,
        'townshipAttribute' => 9,
        'cellarAttribute' => 10,
        'yearAttribute' => 11,
        'wineTypeAttribute' => 12,
        'spiritTypeAttribute' => 13,
        'proofAttribute' => 14,
        'bookEditorAttribute' => 16,
        'mineralDrinkTypeAttribute' => 17,
    ],

    // Key d'une metadonnée sur un produit Woocommerce: Prix sur place
    // Key d'une metadonnée sur un utilisateur Woocommerce: 
    // Key d'une metadonnée sur un utilisateur Woocommerce: 
    // Key d'une metadonnée sur un produit Woocommerce: quantité minimum d'un produit que l'utilisateur du shop peut acheter dans une commande
    // Key d'une metadonnée sur un produit Woocommerce: quantité maximum d'un produit que l'utilisateur du shop peut acheter dans une commande
    // Key d'une metadonnée sur une commande Woocommerce: le GUID d'une commande crée dans TCPOS
    // Key d'une metadonnée sur un produit Woocommerce: Site web associé
    // Key d'une metadonnée sur un produit Woocommerce: email associé
    // Key d'une metadonnée sur un produit Woocommerce: numéro de téléphone associé
    // Key d'une metadonnée sur un utilisateur Woocommerce: type de compte (chatelin ou normal par exemple)
    // Key d'une metadonnée sur un produit Woocommerce: ID TCPOS
    // Key d'une metadonnée sur un produit Woocommerce: url sur le détail d'un produit. Utilisé pour les aggrégations de produits (colis du mois)
    'wc_meta_on_site_price' => 'on_site_price',
    'wc_meta_funds_used' => '_funds_used',
    'wc_meta_account_funds' => 'account_funds',
    'wc_meta_minimum_allowed_quantity' => 'minimum_allowed_quantity',
    'wc_meta_maximum_allowed_quantity' => 'maximum_allowed_quantity',
    'wc_meta_tcpos_order_id' => 'tcpos_order_id',
    'wc_meta_cellar_website' => 'website',
    'wc_meta_cellar_email' => 'email',
    'wc_meta_cellar_phone' => 'phone',
    'wc_meta_customer_account_type' => 'account_type',
    'wc_meta_tcpos_id' => 'tcpos_id',
    'wc_meta_detail_url' => 'detail_url',

    // ID d'un client "Anonyme" définit dans TCPOS, utilisé lorsque un client du shop passe commande sans crée de compte.
    'woocommerce_wooNoUserId' => 15,

    // Configuration pour chaque catégorie de produit.
    'categories' => [
        'wine' => [
            'category_id' => 106, // l'ID de la catégorie dans Woocommerce
            'manage_stock' => true, // Définit si woocommerce doit gérer les stocks. Si valeur a "true", la synchro va tenir compte de la configuration minStockQty
            'min_stock_quantity' => 6, // Définit un seuil minimum de stock au niveau de la synchro. Le produit est supprimé de woocommerce si le stock disponible est plus petit
        ],
        'spirit' => [
            'category_id' => 333,
            'manage_stock' => true,
            'min_stock_quantity' => 3,
        ],
        'beer' => [
            'category_id' => 376,
            'manage_stock' => false,
            //'min_stock_quantity' => 3,
        ],
        'cider' => [
            'category_id' => 377,
            'manage_stock' => false,
            //'min_stock_quantity' => 3,
        ],
        'book' => [
            'category_id' => 378,
            'manage_stock' => false,
            //'min_stock_quantity' => 3,
        ],
        'mineralDrink' => [
            'category_id' => 379,
            'manage_stock' => false,
            //'min_stock_quantity' => 3,
        ],
        'wineSet' => [
            'category_id' => 380,
            'manage_stock' => false,
            //'min_stock_quantity' => 3,
        ],
        'selection' => [
            'category_id' => 393,
            'manage_stock' => false,
            //'min_stock_quantity' => 3,
        ]
    ]

];
