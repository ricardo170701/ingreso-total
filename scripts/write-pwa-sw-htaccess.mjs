import { writeFileSync, mkdirSync } from "node:fs";
import { dirname, join } from "node:path";
import { fileURLToPath } from "node:url";

const __dirname = dirname(fileURLToPath(import.meta.url));
const target = join(__dirname, "..", "public", "build", ".htaccess");

const content = `# Generado por npm run build. Permite SW en /build/sw.js con alcance /.
# Apache + mod_headers. Nginx: location = /build/sw.js { add_header Service-Worker-Allowed /; ... }
<IfModule mod_headers.c>
    <Files "sw.js">
        Header set Service-Worker-Allowed "/"
    </Files>
</IfModule>
`;

mkdirSync(dirname(target), { recursive: true });
writeFileSync(target, content, "utf8");
