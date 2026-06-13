export const openModal = (setOpenModals, modalType) => {
    setOpenModals(prev => new Set([...prev, modalType]));
};

export const closeModal = (setOpenModals, modalType) => {
    setOpenModals(prev => {
        const newSet = new Set(prev);
        newSet.delete(modalType);
        return newSet;
    });
};

export const isModalOpen = (openModals, modalType) => {
    return openModals.has(modalType);
};

export const closeAllModals = (setOpenModals) => {
    setOpenModals(new Set());
};