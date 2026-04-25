export const shuffle = <T>(source: T[]): T[] => {
    const copy = [...source];

    for (let i = copy.length - 1; i > 0; i -= 1) {
        const randomIndex = Math.floor(Math.random() * (i + 1));
        [copy[i], copy[randomIndex]] = [copy[randomIndex], copy[i]];
    }

    return copy;
};
