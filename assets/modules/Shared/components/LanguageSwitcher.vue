<template>
    <div class="relative inline-block text-left">
        <select
            v-model="currentLocale"
            @change="handleLocaleChange"
            class="bg-white border border-gray-300 text-gray-700 py-1 px-3 pr-8 rounded leading-tight focus:outline-none focus:border-merashop-500 text-sm appearance-none cursor-pointer"
        >
            <option v-for="loc in SUPPORTED_LOCALES" :key="loc.code" :value="loc.code">
                {{ loc.flag }} {{ loc.name }}
            </option>
        </select>
        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
            <svg class="fill-current h-4 w-4" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { SUPPORTED_LOCALES } from '@shared/types/locale';
import { LOCALE } from '@shared/constants';


const { locale } = useI18n();
const currentLocale = ref(locale.value);

const handleLocaleChange = () => {
    locale.value = currentLocale.value;

    document.cookie = `${LOCALE.COOKIE_NAME}=${currentLocale.value};path=/;max-age=31536000`;

    window.location.reload();
};
</script>
