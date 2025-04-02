import Translator from 'bazinga-translator';
export default async () => {
    const locale = document.documentElement.lang;
    const res = await fetch(`/translations/messages.json?locales=${locale}`);
    const json = await res.json();
    return Translator.fromJSON(json);
};
