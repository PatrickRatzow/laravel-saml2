<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Service Providers
    |--------------------------------------------------------------------------
    |
    | Each Service Provider (SP) must be given a unique name,
    | which will be used throughout the system, and appended to the routes.
    |
    */

    // What SP to use when none are specified.
    // Leave blank to select first entry.
    'default' => null,

    'test' => [
        // SP name.
        'default' => [
            // Identifier of the SP entity (must be a URI).
            // Leave blank to use the 'saml2.metadata' route.
            'entityId' => null,
            // Specifies info about where and how the <AuthnResponse> message MUST be
            // returned to the requester, in this case our SP.
            'assertionConsumerService' => [
                // URL Location where the <Response> from the IdP will be returned.
                // Leave blank to use the 'saml2.acs' route.
                'url' => null,
                // SAML protocol binding to be used when returning the <Response> message.
                // Onelogin Toolkit supports for this endpoint the HTTP-POST binding only.
                # 'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
            ],
            // If you need to specify requested attributes, set an attributeConsumingService.
            // Attributes nameFormat, attributeValue and friendlyName can be omitted.
            // Otherwise remove this section.
            'attributeConsumingService' => [
                'serviceName'         => 'SP test',      // Can be entity ID.
                'serviceDescription'  => 'Test Service', // Not required.
                'requestedAttributes' => [
                    [
                        'name'           => '',
                        'isRequired'     => false,
                        'nameFormat'     => null,
                        'friendlyName'   => null,
                        'attributeValue' => null,
                    ],
                ],
            ],
            // Specifies info about where and how the <Logout Response> message MUST be
            // returned to the requester, in this case our SP.
            'singleLogoutService' => [
                // URL Location where the <Response> from the IdP will be returned.
                // Leave blank to use the 'saml2.sls' route.
                'url' => null,
                // SAML protocol binding to be used when returning the <Response> message.
                // Onelogin Toolkit supports for this endpoint the HTTP-Redirect binding only.
                # 'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            ],
            // Specifies constraints on the name identifier to be used to
            // represent the requested subject.
            // Take a look on OneLogin\Saml2/Constants.php to see the NameIdFormat supported.
            'NameIDFormat' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified',

            // Usually x509cert and privateKey of the SP are provided by files placed at
            // the certs folder. But we can also provide them with the following parameters
            'x509cert'   => storage_path('certs/test/sp.crt'),
            'privateKey' => storage_path('certs/test/sp.key'),
            'passphrase' => '', // Only specify if private key is encrypted.

            // Key rollover
            // If you plan to update the SP x509cert and privateKey
            // you can define here the new x509cert and it will be
            // published on the SP metadata so Identity Providers can
            // read them and get ready for rollover.
            # 'x509certNew' => storage_path('certs/test/sp_new.crt'),

            // Override any options specified under 'onelogin' settings.
            # 'onelogin' => [
            # ],
        ],
    ],

    'prod' => [
        // SP name.
        'default' => [
            // Identifier of the SP entity (must be a URI).
            // Leave blank to use the 'saml2.metadata' route.
            'entityId' => null,
            // Specifies info about where and how the <AuthnResponse> message MUST be
            // returned to the requester, in this case our SP.
            'assertionConsumerService' => [
                // URL Location where the <Response> from the IdP will be returned.
                // Leave blank to use the 'saml2.acs' route.
                'url' => null,
                // SAML protocol binding to be used when returning the <Response> message.
                // Onelogin Toolkit supports for this endpoint the HTTP-POST binding only.
                # 'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
            ],
            // If you need to specify requested attributes, set an attributeConsumingService.
            // Attributes nameFormat, attributeValue and friendlyName can be omitted.
            // Otherwise remove this section.
            'attributeConsumingService' => [
                'serviceName'         => 'SP prod',      // Can be entity ID.
                'serviceDescription'  => 'Prod Service', // Not required.
                'requestedAttributes' => [
                    [
                        'name'           => '',
                        'isRequired'     => false,
                        'nameFormat'     => null,
                        'friendlyName'   => null,
                        'attributeValue' => null,
                    ],
                ],
            ],
            // Specifies info about where and how the <Logout Response> message MUST be
            // returned to the requester, in this case our SP.
            'singleLogoutService' => [
                // URL Location where the <Response> from the IdP will be returned.
                // Leave blank to use the 'saml2.sls' route.
                'url' => null,
                // SAML protocol binding to be used when returning the <Response> message.
                // Onelogin Toolkit supports for this endpoint the HTTP-Redirect binding only.
                # 'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            ],
            // Specifies constraints on the name identifier to be used to
            // represent the requested subject.
            // Take a look on OneLogin\Saml2/Constants.php to see the NameIdFormat supported.
            'NameIDFormat' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified',

            // Usually x509cert and privateKey of the SP are provided by files placed at
            // the certs folder. But we can also provide them with the following parameters
            'x509cert'   => storage_path('certs/prod/sp.crt'),
            'privateKey' => storage_path('certs/prod/sp.key'),
            'passphrase' => '', // Only specify if private key is encrypted.

            // Key rollover
            // If you plan to update the SP x509cert and privateKey
            // you can define here the new x509cert and it will be
            // published on the SP metadata so Identity Providers can
            // read them and get ready for rollover.
            # 'x509certNew' => storage_path('certs/prod/sp_new.crt'),

            // Override any options specified under 'onelogin' settings.
            # 'onelogin' => [
            # ],
        ],
    ],

];
