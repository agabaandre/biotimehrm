-- Store BioTime resign id so update/transfer can reinstate when facility area becomes available.
ALTER TABLE biotime_enrollment
  ADD COLUMN IF NOT EXISTS biotime_resign_id VARCHAR(50) NULL DEFAULT NULL AFTER biotime_fac_id;
