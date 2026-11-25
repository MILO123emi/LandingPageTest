export type AdminSite = {
    slug: string;
    name: string;
    path: string;
    status: 'draft' | 'live';
    adminUrl: string;
    publicUrl: string;
};
