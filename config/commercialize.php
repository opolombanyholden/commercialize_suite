<?php

return [

    /*
    |--------------------------------------------------------------------------
    | CommercialiZe Suite Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration des features et limites par version
    |
    */

    'features' => [
        'light' => [
            // Documents
            'create_quotes' => true,
            'create_invoices' => true,
            'create_deliveries' => true,
            'generate_pdf' => true,

            // Taxes
            'manage_taxes' => true,

            // Limitations
            'save_products' => false,
            'save_clients' => false,
            'history' => false,
            'ecommerce' => false,
            'social_sharing' => false,
            'multi_users' => false,
            'multi_sites' => false,
            'inventory' => false,
            'payments_tracking' => false,
            'reports' => false,
        ],

        'standard' => [
            // Hérite de Light
            'create_quotes' => true,
            'create_invoices' => true,
            'create_deliveries' => true,
            'generate_pdf' => true,
            'manage_taxes' => true,

            // Nouvelles features
            'save_products' => true,
            'save_clients' => true,
            'history' => true,
            'search' => true,
            'edit_documents' => true,
            'reports_basic' => true,
            'export_excel' => true,

            // Toujours désactivé
            'ecommerce' => false,
            'social_sharing' => false,
            'multi_users' => false,
            'multi_sites' => false,
            'inventory' => false,
            'payments_tracking' => false,
            'reports_advanced' => false,
        ],

        'pro' => [
            // Hérite de Standard
            'create_quotes' => true,
            'create_invoices' => true,
            'create_deliveries' => true,
            'generate_pdf' => true,
            'manage_taxes' => true,
            'save_products' => true,
            'save_clients' => true,
            'history' => true,
            'search' => true,
            'edit_documents' => true,
            'reports_basic' => true,
            'export_excel' => true,

            // Nouvelles features
            'inventory' => true,
            'stock_alerts' => true,
            'payments_tracking' => true,
            'payment_schedules' => true,
            'ecommerce' => true,
            'online_store' => true,
            'social_sharing' => true,
            'product_variants' => true,
            'categories' => true,
            'reports_advanced' => true,
            'multi_users_limited' => true, // Max 3 utilisateurs

            // Toujours désactivé
            'multi_sites' => false,
            'multi_users_unlimited' => false,
            'advanced_permissions' => false,
            'audit_logs' => false,
            'custom_domains' => false,
        ],

        'enterprise' => [
            // TOUTES les fonctionnalités
            'create_quotes' => true,
            'create_invoices' => true,
            'create_deliveries' => true,
            'generate_pdf' => true,
            'manage_taxes' => true,
            'save_products' => true,
            'save_clients' => true,
            'history' => true,
            'search' => true,
            'edit_documents' => true,
            'reports_basic' => true,
            'export_excel' => true,
            'inventory' => true,
            'stock_alerts' => true,
            'payments_tracking' => true,
            'payment_schedules' => true,
            'ecommerce' => true,
            'online_store' => true,
            'social_sharing' => true,
            'product_variants' => true,
            'categories' => true,
            'reports_advanced' => true,

            // Features Entreprise
            'multi_sites' => true,
            'multi_users_unlimited' => true,
            'advanced_permissions' => true,
            'role_management' => true,
            'audit_logs' => true,
            'stock_transfers' => true,
            'custom_domains' => true,
            'api_access' => true,
            'webhooks' => true,
            'priority_support' => true,
            'custom_branding' => true,
            'sso' => true,
            'data_export' => true,
            'custom_reports' => true,
        ],
    ],

    'limits' => [
        'light' => [
            'users' => 1,
            'sites' => 1,
            'products' => 0,
            'clients' => 0,
            'documents_per_month' => 999999,
        ],

        'standard' => [
            'users' => 1,
            'sites' => 1,
            'products' => 1000,
            'clients' => 500,
            'documents_per_month' => 100,
        ],

        'pro' => [
            'users' => 3,
            'sites' => 1,
            'products' => 5000,
            'clients' => 2000,
            'documents_per_month' => 999999,
        ],

        'enterprise' => [
            'users' => 999999,
            'sites' => 999999,
            'products' => 999999,
            'clients' => 999999,
            'documents_per_month' => 999999,
        ],
    ],

    'pricing' => [
        'light' => [
            'monthly_price' => 0,
            'currency' => 'XAF',
            'paywall_per_document' => 500,
        ],
        'standard' => [
            'monthly_price' => 15000,
            'currency' => 'XAF',
        ],
        'pro' => [
            'monthly_price' => 35000,
            'currency' => 'XAF',
        ],
        'enterprise' => [
            'monthly_price' => 75000,
            'currency' => 'XAF',
            'custom_pricing' => true,
        ],
    ],

    'document_numbering' => [
        'quote' => [
            'prefix' => 'Q',
            'format' => 'Q-YYYYMMDD-XXXXX',
        ],
        'invoice' => [
            'prefix' => 'F',
            'format' => 'F-YYYYMMDD-TIMESTAMP',
        ],
        'delivery_note' => [
            'prefix' => 'BL',
            'format' => 'BL-YYYYMMDD-XXXXX',
        ],
    ],

    'pdf' => [
        'default_options' => [
            'orientation' => 'portrait',
            'page-size' => 'A4',
            'margin-top' => 10,
            'margin-right' => 10,
            'margin-bottom' => 10,
            'margin-left' => 10,
            'dpi' => 300,
            'image-quality' => 100,
        ],
        'storage_path' => 'pdf',
        'cache_enabled' => true,
        'cache_duration' => 3600,
    ],

    'ecommerce' => [
        'subdomain_pattern' => '{slug}.shop.commercialize.com',
        'default_shipping_zones' => [
            'Libreville' => 2000,
            'Port-Gentil' => 5000,
            'Reste Gabon' => 10000,
        ],
        'payment_methods' => [
            'cash_on_delivery',
            'bank_transfer',
            'mobile_money',
            'credit_card',
        ],
    ],

    'taxes' => [
        'default_country' => 'GA',
        'predefined' => [
            [
                'name' => 'TPS Standard',
                'rate' => 18.00,
                'description' => 'Taxe sur la Prestation de Services - Taux standard',
                'apply_to' => 'all',
                'is_active' => true,
            ],
            [
                'name' => 'TPS Réduite',
                'rate' => 10.00,
                'description' => 'Taxe sur la Prestation de Services - Taux réduit',
                'apply_to' => 'all',
                'is_active' => true,
            ],
            [
                'name' => 'Exonéré',
                'rate' => 0.00,
                'description' => 'Exonération de taxe',
                'apply_to' => 'all',
                'is_active' => true,
            ],
        ],
    ],

];
