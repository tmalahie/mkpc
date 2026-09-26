-- Turns off the MySQL events that php/cron/ replaces. Run once the crontab is installed and each
-- job has logged a successful run (journalctl -t mkpc-cron). DISABLE rather than DROP, so going
-- back is the same four lines with ENABLE, and removing /etc/cron.d/mkpc.
ALTER EVENT `mkcleanonline` DISABLE;
ALTER EVENT `mkemptytrash` DISABLE;
ALTER EVENT `mkunban` DISABLE;
ALTER EVENT `mkrecomputerating` DISABLE;
