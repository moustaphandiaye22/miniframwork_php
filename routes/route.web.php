<?php
// Tableau de routes sans le préfixe /api
return [
    // Health Check
    [
        'method' => 'GET',
        'path' => '/health',
        'action' => function() {
            header('Content-Type: application/json');
            echo json_encode([
                'data' => ['status' => 'ok', 'timestamp' => date('Y-m-d H:i:s')],
                'statut' => 'success',
                'code' => 200,
                'message' => 'AppDAF API is running'
            ]);
        }
    ],
    // Recherche citoyen par NCI (URL param)
    [
        'method' => 'GET',
        'path' => '/citoyen/nci/{nci}',
        'controller' => 'CitoyenController',
        'action' => 'findByNci'
    ],
    // Recherche citoyen par NCI (Query param)
    [
        'method' => 'GET',
        'path' => '/citoyen',
        'controller' => 'CitoyenController',
        'action' => 'show'
    ],
    // Liste des citoyens
    [
        'method' => 'GET',
        'path' => '/citoyens',
        'controller' => 'CitoyenController',
        'action' => 'index'
    ],
    // Création d'un citoyen
    [
        'method' => 'POST',
        'path' => '/citoyens',
        'controller' => 'CitoyenController',
        'action' => 'store'
    ]
];




