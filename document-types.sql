-- Inserting document types commonly used in Latin America with snake_case labels
INSERT INTO "hr"."document_type" ("name", "label") VALUES
  ('passport', 'document_type_passport'),
  ('id_card', 'document_type_id_card'),
  ('driver_license', 'document_type_driver_license'),
  ('voter_id', 'document_type_voter_id'),
  ('tax_identification_number', 'document_type_tax_identification_number'),
  ('birth_certificate', 'document_type_birth_certificate'),
  ('residence_permit', 'document_type_residence_permit'),
  ('curp', 'document_type_curp'),  -- Mexico
  ('dni', 'document_type_dni'),    -- Argentina, Peru
  ('cpf', 'document_type_cpf'),    -- Brazil
  ('cnh', 'document_type_cnh'),    -- Brazil (Driver’s License)
  ('rfc', 'document_type_rfc'),    -- Mexico (Taxpayer Registration)
  ('cedula', 'document_type_cedula'), -- Colombia, Venezuela
  ('rut', 'document_type_rut'),    -- Chile (Tax Identification)
  ('ine', 'document_type_ine'),    -- Mexico (Voter ID)
  ('cuil', 'document_type_cuil'),  -- Argentina (Tax Identification)
  ('nit', 'document_type_nit');    -- Guatemala, Colombia (Tax Identification)
