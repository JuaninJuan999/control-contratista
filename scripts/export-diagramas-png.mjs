import fs from 'fs';
import { execFileSync } from 'child_process';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const root = path.join(__dirname, '..');
const md = fs.readFileSync(path.join(root, 'DIAGRAMA_FLUJO.md'), 'utf8');
const mmdDir = path.join(root, 'docs', 'diagramas', 'mmd');
const pngDir = path.join(root, 'docs', 'diagramas', 'png');

const names = [
    '01-flujo-general',
    '02-auth-permisos',
    '03-alta-empresa',
    '04-planilla-ss-dependiente',
    '05-planilla-ss-independiente',
    '06-gestion-contratista',
    '07-control-mensual',
    '08-alertas-automaticas',
    '09-operacion-diaria-siso',
    '10-importacion-excel',
    '11-peticion-http',
    '12-mapa-planilla-ss',
];

fs.mkdirSync(mmdDir, { recursive: true });
fs.mkdirSync(pngDir, { recursive: true });

const re = /```mermaid\n([\s\S]*?)\n```/g;
let match;
let index = 0;

while ((match = re.exec(md)) !== null) {
    const name = names[index] ?? `diagrama-${index + 1}`;
    const mmdPath = path.join(mmdDir, `${name}.mmd`);
    const pngPath = path.join(pngDir, `${name}.png`);

    fs.writeFileSync(mmdPath, `${match[1].trim()}\n`);

    execFileSync(
        'npx',
        ['@mermaid-js/mermaid-cli', '-i', mmdPath, '-o', pngPath, '-b', 'white', '-s', '2'],
        { cwd: root, stdio: 'inherit', shell: true },
    );

    console.log(`OK ${name}.png`);
    index++;
}

console.log(`Generados ${index} PNG en docs/diagramas/png/`);
