-- Migration pour créer la table citoyens
CREATE TABLE IF NOT EXISTS citoyens (
    id SERIAL PRIMARY KEY,
    nci VARCHAR(20) UNIQUE NOT NULL,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    date_naissance DATE NOT NULL,
    lieu_naissance VARCHAR(200) NOT NULL,
    url_photo_identite VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Index sur le NCI pour optimiser les recherches
CREATE INDEX idx_citoyens_nci ON citoyens(nci);

-- Trigger pour mettre à jour updated_at automatiquement
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ language 'plpgsql';

CREATE TRIGGER update_citoyens_updated_at 
    BEFORE UPDATE ON citoyens 
    FOR EACH ROW 
    EXECUTE FUNCTION update_updated_at_column();
