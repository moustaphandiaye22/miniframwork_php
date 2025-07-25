-- Migration pour créer la table de journalisation
INSERT into citoyen (nci, nom, prenom, date_naissance, lieu_naissance, sexe, adresse, email, telephone, created_at)
VALUES
    ('NCI123456', 'DUPONT', 'Jean', '1990-01-01', 'Paris', 'M', '1 rue de Paris, 75000 Paris', 'jean.dupont@example.com', '0123456789', NOW()),
    ('NCI123457', 'MARTIN', 'Sophie', '1985-05-15', 'Lyon', 'F', '2 avenue de Lyon, 69000 Lyon', 'sophie.martin@example.com', '0987654321', NOW());
-- Création de la table logs
    INSERT into logs (id, nci, action, description, created_at)
VALUES
    (1, 'NCI123456', 'CREATE', 'Création de l\'utilisateur Jean DUPONT', NOW()),
    (2, 'NCI123457', 'CREATE', 'Création de l\'utilisateur Sophie MARTIN', NOW());  
    