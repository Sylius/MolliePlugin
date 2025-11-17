# AUDIT-IGNORE

This document explains why specific advisories are added to `composer.json` → `config.audit.ignore`.

- PKSA-gs8r-6kz6-pp56 — api-platform/core ^2.7.x required by sylius/sylius ~1.13.x and ~1.14.x; affected versions are pulled by the pinned Sylius constraints.

- PKSA-gnn4-pxdg-q76m — same as above, applies to api-platform/core in Sylius ~1.13.x and ~1.14.x matrices.

- PKSA-4g5g-4rkv-myqs — enshrined/svg-sanitize ^0.16 required transitively by sylius/sylius ~1.13.x; audit blocks installation, ignore until dependencies are upgraded.

We ignore these advisories temporarily because our build matrix pins Sylius versions that still require the affected ranges; once upstream is updated (or we upgrade Sylius), the ignores should be removed.
