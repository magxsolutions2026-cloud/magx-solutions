# MAGX Solutions — portfolio site

Freelance portfolio for **Mark Angelo Guillermo / MAGX Solutions**: a React + Three.js
frontend (`web/`) in front of the existing PHP backend (admin panel, appointments,
content APIs).

> **Work in progress on branch `redesign/react-frontend`.**
> Phases 1–5 are done; phase 6 (performance & deploy polish) is next.
> Jump to [Continue here](#continue-here).

---

## Quick start

```bash
cd web
npm install          # slow/flaky network? see "Troubleshooting"
npm run dev          # http://localhost:5173/site/
```

| Script | What it does |
|---|---|
| `npm run dev` | Vite dev server (the app is served under `/site/`) |
| `npm run build` | Typecheck + production build into `../site/` (git-ignored) |
| `npm run preview` | Serve the production build locally |
| `npm run typecheck` | TypeScript only |
| `npm run build:avatar` | Rebuild avatar + animation assets (see [Avatar pipeline](#avatar-pipeline)) |

**PHP backend in dev:** Vite proxies `/*_api.php` and `/uploads/*` to `http://localhost:8000`
(override with `VITE_PHP_PROXY`). PHP is **not installed on this machine**, so locally:

- Services and projects fall back to the static copy in `web/src/content/site.ts`.
- Booking times show "Times unavailable", and the contact form can't send.

That's expected. To run the backend: `php -S localhost:8000` in the repo root
(needs PHP + `composer install` + a `.env`; see `.env.example`).

---

## How the site works

One fixed, full-screen WebGL canvas sits **behind** normal HTML sections. Scrolling
moves a camera through one 3D scene:

| Section | Camera shot | 3D content |
|---|---|---|
| Hero | Workspace on the right (lens shift) | Avatar typing at a desk |
| Services | Rises, looks into the particle field | Particles only |
| Work | Above the desk, looking at the deck | Interactive project cards |
| Proof, Stack | Render loop **paused** (opaque sections) | — |
| Contact | In front of the standing avatar | Avatar swivels, stands up, points/waves |

Key behaviours:

- **Camera** (`canvas/CameraRig.tsx`, `canvas/cameraPath.ts`): per-section shots scrubbed
  by GSAP ScrollTrigger. The camera eases toward them with cursor parallax. `frameX` is a
  lens shift that keeps the subject clear of the text. Phones get their own shots and a wider FOV.
- **Particles** (`canvas/particles/`): GPU-animated points (custom GLSL) that swirl and
  pulse when the active section changes.
- **Avatar** (`canvas/workspace/Avatar.tsx`, `Workspace.tsx`): an animation state machine.
  - **Seated:** typing.
  - **At Contact:** the chair swivels out and he stands up (Mixamo "Stand Up", with
    root motion), then breathes, looks around and follows the cursor.
  - **On the form:** hovering or focusing fields makes him **point**; the Send button
    makes him **wave**.
  - **Leaving Contact** reverses the stand-up and he sits back down.
- **Work deck** (`canvas/work/`): cards on an arc, driven by scroll through a sticky track.
  - **Hover shader:** ripple, RGB split, flare, sheen and an edge glow.
  - **Pointer events:** the canvas gets them from `#root` (`eventSource`) because it sits
    behind the page.
- **Performance:**
  - three.js/R3F load lazily, so first load is about 165 KB gzipped.
  - The preloader is static HTML/SVG in `web/index.html`, driven by `usePreloader`.
  - The render loop pauses when only `data-scene="off"` sections are on screen or the
    tab is hidden.
  - Reduced motion: no camera glides, still particles, no reveals.
- **Content:**
  - Services and projects come from the admin panel via `public_content_api.php`,
    falling back to `web/src/content/site.ts`.
  - All copy and claims come from the legacy homepage. **Don't invent numbers, clients
    or quotes.**
  - The testimonials row only appears once real quotes are added to `site.ts`.

### Backend endpoints used by the new site

| Endpoint | Purpose |
|---|---|
| `public_content_api.php` (new) | Read-only active services, portfolio, footer (`action=ALL`) |
| `inquiries_api.php` (new) | Contact form → `inquiries` table + PHPMailer alert. Honeypot + session rate limit |
| `appointments_api.php` (existing) | Booking dialog: `AVAILABLE_SLOTS`, `SUBMIT` (admin approval flow unchanged) |

---

## Project structure

```
web/
  index.html                 static SVG pre-loader lives here
  avatar-lab.html            dev-only avatar harness (not in production build)
  vite.config.ts             base /site/, output ../site, chunking (keeps 3D out of first load)
  scripts/build-avatar.mjs   avatar/clip pipeline
  assets-src/avatar/         SOURCE files: avatar.glb (Avaturn) + Mixamo .fbx clips
  public/models/avatar/      GENERATED: avatar.glb (compressed) + anims.json
  public/work/               project cover images (WebP)
  src/
    App.tsx                  section order
    content/site.ts          all copy, credentials, projects fallback, tech stack
    lib/api.ts               PHP API client
    lib/gsap.ts              GSAP/ScrollTrigger/Lenis setup, scrollToId/scrollToY
    store/                   sceneStore (section, avatar state/pose), uiStore (booking dialog)
    hooks/                   preloader, active section, reveal, canvas visibility, viewport, avatar cues
    components/              Section, MagneticButton, BookingDialog, ContactForm, SceneErrorBoundary
    sections/                Navbar, Hero, Services, Work, Proof, TechStack, Contact, Footer
    canvas/                  SceneCanvas, CameraRig, cameraPath, particles/, workspace/, work/
    dev/AvatarLab.tsx        ?mode=stand|desk&clip=wave|point|look&probe
```

---

## Avatar pipeline

Avatar from **Avaturn** ("Avatar (T-Pose)" export). Clips from **Mixamo**: FBX Binary,
**Without Skin**, 30 fps.

1. Put the files in `web/assets-src/avatar/` with these names: `avatar.glb`, `typing.fbx`,
   `breathing-idle.fbx`, `looking-around.fbx`, `waving.fbx`, `pointing.fbx`, `stand-up.fbx`.
2. Run `npm run build:avatar`. It:
   - retargets the clips by bone name (`mixamorig:Hips` → `Hips`);
   - rescales hip height to the avatar;
   - extracts Stand Up's forward travel as root motion;
   - compresses the GLB (meshopt + WebP textures).
3. If the new avatar has different proportions, re-measure the hands. Open
   `avatar-lab.html?mode=desk&probe` and read `window.__hands`. The desk/laptop sizes in
   `Workspace.tsx` are based on that.

---

## Dev & testing tips

- `window.__scene` (dev only) is the scene store, e.g. `__scene.getState().setAvatarState('wave')`.
- `?__scroll=0.5` (dev only) jumps to that fraction of the page after load.
- **Headless Chrome tests** use `/usr/bin/google-chrome` with `--use-angle=swiftshader`.
  - Software rendering runs at only ~4 fps, so wait on store state (`__scene`) rather than
    fixed delays.
  - Puppeteer `clip` uses document coordinates (add `scrollY`).
- **Troubleshooting:** the npm registry connection here is slow and flaky. Use
  `npm_config_fetch_retries=6 npm_config_fetch_timeout=600000 npm install`. On `ENOTEMPTY`,
  delete `web/node_modules` and reinstall.

---

## Deployment (Vercel)

- `vercel.json` builds `web/` (`buildCommand`) into `site/`.
- `/` and unknown paths serve `site/index.html`; the PHP APIs keep their routes.
- **The legacy homepage and admin login stay at `/index.php`.** Admin login is now at
  **`/index.php?admin=1`** (not `/?admin=1`).

**Before going live:**

- [ ] Run `docs/inquiries_schema.sql` on Supabase (the contact form needs the `inquiries` table).
- [ ] Deploy this branch as a **Vercel preview** and check:
  - [ ] `/` serves the new site;
  - [ ] the `/site/assets/*` files and `/uploads/*` load;
  - [ ] the APIs respond;
  - [ ] the admin login works.
  - The routing hasn't been tested on Vercel yet.
- [ ] Confirm the SMTP env vars (`SMTP_*`, `ADMIN_NOTIFICATION_EMAIL`) are set for inquiry emails.

---

## Continue here

### Done (commits on `redesign/react-frontend`)

1. **Phase 1:** scaffold, pre-loader, backend endpoints, avatar pipeline.
2. **Phase 2:** scroll camera, particles, workspace, render-loop pausing, responsive FOV.
3. **Phase 3:** hero + magnetic CTA, services, proof, tech stack, booking dialog.
4. **Phase 4:** 3D work deck with hover shader.
5. **Phase 5:** contact form plus avatar choreography (swivel, stand up, point, wave, sit back
   down); jacket avatar; front light at Contact.

### Next: Phase 6, performance & polish

- [ ] **Lighthouse** (mobile + desktop) on the production build; fix LCP/CLS/TBT findings.
- [ ] **Accessibility pass:**
  - keyboard through nav, booking dialog, work deck prev/next, contact form;
  - focus styles and contrast of `text-muted`;
  - screen-reader check of the Work list.
- [ ] **Low-end phones:**
  - test on a real device;
  - consider an `AdaptiveDpr`/`PerformanceMonitor` step-down;
  - consider fewer particles or no shadows when FPS drops.
- [ ] **SEO/meta:** Open Graph image + tags, favicon set, `robots`/sitemap.
- [ ] **Vercel preview deploy** and the checklist above.
- [ ] **Remove leftovers:**
  - the unused `hoveredProject` in `sceneStore`;
  - the unused `public/work/drrm-award.webp`, or use it on the Proof card.

### Open items for Mark (content/decisions)

- [ ] **TMC cover image** (`uploads/portfolio/tmc/tmc-mockup-01.jpg`) says "MAGH SOLUTIONS". That's a typo in the source image.
- [ ] **Firstgate cover** is a small Facebook-page screenshot; a real campaign graphic would look better.
- [ ] **Real client testimonials:** add them to `testimonials` in `web/src/content/site.ts`.
- [ ] **Pricing/response claims:** "free", "30 min" and "reply within a day" were removed on purpose. Re-add only if true.
- [ ] **Pointing direction:** the avatar points at the visitor, not at the form. Keep it, or rotate him toward the form?
- [ ] **Stand-up seat height:** the Stand Up clip assumes a slightly taller chair, so he shifts up as he starts rising. Check that it looks OK in a real browser.
- [ ] **Security (separate task):** admin passwords are stored and compared in plaintext (`index.php` login, `tbl_acc`).
