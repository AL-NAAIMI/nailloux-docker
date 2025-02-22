import sys
import json
from PIL import Image, ExifTags

def clean_exif_value(value):
    """
    Nettoie les valeurs EXIF pour les rendre compatibles JSON.
    """
    if isinstance(value, bytes):
        # Décoder les valeurs bytes en chaîne UTF-8, en remplaçant les caractères invalides
        return value.decode('utf-8', errors='replace')
    elif isinstance(value, (list, tuple)):
        # Appliquer le nettoyage récursivement sur les listes ou tuples
        return [clean_exif_value(v) for v in value]
    elif isinstance(value, dict):
        # Appliquer le nettoyage récursivement sur les dictionnaires
        return {k: clean_exif_value(v) for k, v in value.items()}
    return value  # Retourner la valeur brute si elle est déjà propre

def extract_metadata(image_path):
    try:
        with Image.open(image_path) as img:
            metadata = {
                "Image Format": img.format,
                "Image Size": img.size,
                "Image Mode": img.mode
            }

            exif_data = img.getexif()
            if exif_data:
                exif_metadata = {}
                for tag, value in exif_data.items():
                    tag_name = ExifTags.TAGS.get(tag, f"Unknown Tag {tag}")
                    exif_metadata[tag_name] = clean_exif_value(value)
                metadata["EXIF Data"] = exif_metadata
            else:
                metadata["EXIF Data"] = {}

            return json.dumps(metadata, indent=4, default=str)
    except Exception as e:
        return json.dumps({"error": str(e)})

if __name__ == "__main__":
    if len(sys.argv) != 2:
        print(json.dumps({"error": "Usage: python extract_metadata.py <image_path>"}))
        sys.exit(1)

    image_path = sys.argv[1]
    print(extract_metadata(image_path))
