-- Remove nobreaks criados automaticamente por bateria de notebook (nome so numeros).
-- Execute no MySQL do Render/phpMyAdmin se ainda aparecer card fantasma em /ups.

DELETE FROM leituras WHERE sensor_id IN (
    SELECT s.id FROM sensores s
    JOIN dispositivos d ON d.id = s.dispositivo_id
    WHERE d.tipo = 'nobreak' AND d.nome REGEXP '^[0-9]+$'
);

DELETE FROM sensores WHERE dispositivo_id IN (
    SELECT id FROM dispositivos WHERE tipo = 'nobreak' AND nome REGEXP '^[0-9]+$'
);

DELETE FROM dispositivos WHERE tipo = 'nobreak' AND nome REGEXP '^[0-9]+$';
