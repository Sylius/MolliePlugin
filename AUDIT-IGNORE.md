# Composer audit ignore – rationale

This project is pinned to Sylius v1.13.x, which pulls transitive dependencies that are currently flagged by Packagist Security Advisories. To keep CI/builds green while we work within the required version constraints, add the following temporary ignores in `composer.json`:

```
{
  "config": {
    "audit": {
      "ignore": [
        "PKSA-gs8r-6kz6-pp56",
        "PKSA-gnn4-pxdg-q76m",
        "PKSA-4g5g-4rkv-myqs"
      ]
    }
  }
}
```

Why these IDs
- PKSA-gs8r-6kz6-pp56, PKSA-gnn4-pxdg-q76m: advisories on `api-platform/core` (required by `sylius/sylius` ~1.13.11). Composer fails audit because those versions are flagged but still required by our Sylius constraint.
- PKSA-4g5g-4rkv-myqs: advisory on `enshrined/svg-sanitize` ^0.16 (required by `sylius/sylius` ~1.13.11). Audit blocks install unless ignored.

Notes
- These ignores are scoped to allow installation under the current Sylius line; remove them when upgrading Sylius to a version that pulls non-flagged dependency ranges.
- We are not disabling audit globally (`block-insecure` remains off); we only ignore specific advisories tied to our pinned upstream.
