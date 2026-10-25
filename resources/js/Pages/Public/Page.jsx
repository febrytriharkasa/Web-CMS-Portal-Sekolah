import PublicLayout from '@/Layouts/PublicLayout';
import Seo from '@/Components/Seo';
import Breadcrumbs from '@/Components/Breadcrumbs';
import DataTable from '@/Components/DataTable';
import RichContent from '@/Components/RichContent';
import { blocksFromMetadata } from '@/lib/postBlocks';
import { plainText } from '@/lib/content';
import { Reveal } from '@/Components/Reveal';

export default function Page({ page, draftPreview = false }) {
    const blocks = blocksFromMetadata(page.metadata);

    return (
        <PublicLayout>
            <Seo
                title={draftPreview ? `${page.title} (draf)` : page.title}
                description={plainText(page.content).slice(0, 160)}
                image={page.image}
                robots={draftPreview ? 'noindex, nofollow' : 'index, follow'}
            />
            <Breadcrumbs items={[{ label: 'Beranda', href: '/' }, { label: page.title }]} />
            <Reveal>
            <div className="block w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                {draftPreview && (
                    <div className="mb-6 flex flex-wrap items-center gap-x-3 gap-y-1 rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                        <span className="font-bold">Pratinjau draf</span>
                        <span className="text-amber-700">Laman ini belum diterbitkan: pengunjung belum bisa membukanya.</span>
                        <a href={`/admin/pages/${page.id}/edit`} className="font-semibold text-primary hover:underline">
                            Edit laman
                        </a>
                    </div>
                )}
                <div className="mb-4">
                    <p className="mt-2 text-xs font-bold uppercase tracking-[0.14em] text-secondary">Tentang Kami</p>
                </div>

                <article className="w-full bg-white rounded-xl p-8 border border-slate-200/80 shadow-card space-y-6">
                    <h1 className="text-3xl md:text-4xl font-extrabold text-slate-900 leading-tight">
                        {page.title}
                    </h1>

                    {page.image && (
                        <img
                            src={page.image}
                            alt={page.title}
                            loading="eager"
                            decoding="async"
                            className="w-full max-h-[28rem] rounded-xl object-cover border border-slate-200"
                        />
                    )}

                    <RichContent
                        html={page.content}
                        className="text-slate-700 leading-relaxed space-y-4"
                    />

                    {blocks.map((block, index) => {
                        if (block.type === 'text') {
                            return (
                                <RichContent
                                    key={index}
                                    html={block.content}
                                    className="text-slate-700 leading-relaxed"
                                />
                            );
                        }
                        if (block.type === 'table') {
                            return (
                                <DataTable
                                    key={index}
                                    title={block.title}
                                    description={block.description}
                                    columns={block.columns}
                                    rows={block.rows}
                                />
                            );
                        }
                        if (block.type === 'callout') {
                            return (
                                <aside
                                    key={index}
                                    className="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-900"
                                >
                                    <RichContent html={block.content} className="text-sm leading-relaxed" />
                                </aside>
                            );
                        }
                        if (block.type === 'blockquote') {
                            return (
                                <blockquote
                                    key={index}
                                    className="border-l-4 border-primary/40 pl-4 text-base italic text-slate-600 leading-relaxed"
                                >
                                    <RichContent html={block.content} />
                                    {block.source && (
                                        <cite className="mt-2 block text-sm not-italic text-slate-500">— {block.source}</cite>
                                    )}
                                </blockquote>
                            );
                        }

                        return null;
                    })}
                </article>
            </div>
            </Reveal>
        </PublicLayout>
    );
}
