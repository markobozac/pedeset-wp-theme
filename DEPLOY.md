# Deploy (GitHub Actions → A2 hosting)

Svaki push na `main` provjeri PHP sintaksu pa rsync-om kopira temu u
`/home/strkacom/public_html/markobozac/50godina/wp-content/themes/pedeset/`.
Datoteke se samo dodaju/prepisuju, nikad brišu (OG slika i ikone žive samo na serveru).
Ručno pokretanje: Actions → "Deploy na A2 hosting" → Run workflow.

## Jednokratno postavljanje
1. **cPanel → Security → SSH Access → Manage SSH Keys → Generate a New Key**
   (tip ed25519, bez lozinke, npr. naziv `github_deploy`) → **Authorize** ključ.
2. U istom ekranu **View/Download** privatni ključ (cijeli tekst, s BEGIN/END redcima).
3. GitHub repo → **Settings → Secrets and variables → Actions → New repository secret**:
   - `SSH_KEY` – privatni ključ iz koraka 2
   - `SSH_HOST` – npr. `nl1-ss106.a2hosting.com`
   - `SSH_USER` – `strkacom`
   - `SSH_PORT` – port SSH-a (A2 obično `7822`)
4. Actions → **Run workflow** za probu.

Ako SSH nije uključen na računu, treba ga zatražiti od A2 podrške.
