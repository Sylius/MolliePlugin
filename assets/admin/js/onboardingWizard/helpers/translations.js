import Translator from 'bazinga-translator';
export default async () => {
    const locale = document.documentElement.lang;
    const locales = [locale];
    if (locale.length > 2) {
        locales.push(locale.slice(0, 2));
    }

    const res = await fetch(`/translations/messages.json?locales=${locales.join(',')}`);
    const json = await res.json();
    return Translator.fromJSON(json);
};
