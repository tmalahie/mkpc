let terser;
try {
    terser = require("terser");
} catch (e) {
    console.error("\u001b[31mTerser is not installed!\u001b[0m");
    process.exit(1);
}
const fs = require("fs");

const inputCode = fs.readFileSync("./scripts/mk.js", "utf8");

console.log("\u001b[33mCompressing mk.js...\u001b[0m");

let minified;
try {
    minified = terser.minify_sync(inputCode, {
        compress: true,
        sourceMap: {
            filename: "mk.min.js",
            url: "mk.min.js.map"
        }
    });
    if (minified.error) {
        throw minified.error;
    }

    if (minified.map) {
        fs.writeFileSync("./scripts/mk.min.js.map", minified.map, "utf8");
    }
} catch (error) {
    console.error("\u001b[31mError during minification:\u001b[0m", error);
    process.exit(1);
}

const originalSize = Buffer.byteLength(inputCode, 'utf8');
const compressedSize = Buffer.byteLength(minified.code, 'utf8');
const reduction = ((originalSize - compressedSize) / originalSize * 100).toFixed(2);

fs.writeFileSync("./scripts/mk.min.js", minified.code, "utf8");

console.log(`\u001b[33mOriginal size:\u001b[0m ${originalSize} bytes`);
console.log(`\u001b[32mCompressed size:\u001b[0m ${compressedSize} bytes`);
console.log(`\u001b[36mReduction:\u001b[0m ${reduction}%`);
console.log("\u001b[32mmk.min.js created successfully!\u001b[0m");
process.exit(0);