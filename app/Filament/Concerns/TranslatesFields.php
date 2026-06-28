<?php

namespace App\Filament\Concerns;

/**
 * يتيح تحرير الحقول المترجمة (spatie/translatable) في لوحة Filament:
 * الحقل الأساسي = العربية، والحقل {field}_en = الإنجليزية.
 * يُستخدم في صفحات Create/Edit للمورد.
 */
trait TranslatesFields
{
    protected function translatableAttributes(): array
    {
        $model = $this->getModel();

        return (new $model)->getTranslatableAttributes();
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->record ?? null;
        if (! $record) {
            return $data;
        }

        foreach ($this->translatableAttributes() as $field) {
            $data[$field] = $record->getTranslation($field, 'ar', false);
            $data[$field.'_en'] = $record->getTranslation($field, 'en', false);
        }

        return $data;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->packTranslations($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->packTranslations($data);
    }

    protected function packTranslations(array $data): array
    {
        foreach ($this->translatableAttributes() as $field) {
            if (! array_key_exists($field, $data) && ! array_key_exists($field.'_en', $data)) {
                continue;
            }

            $ar = $data[$field] ?? null;
            $en = $data[$field.'_en'] ?? null;

            $data[$field] = array_filter(
                ['ar' => $ar, 'en' => $en],
                fn ($v) => $v !== null && $v !== ''
            );

            unset($data[$field.'_en']);
        }

        return $data;
    }
}
