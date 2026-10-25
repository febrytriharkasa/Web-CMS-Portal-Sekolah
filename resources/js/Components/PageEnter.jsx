export default function PageEnter({ children, className = '', keyValue }) {
    return (
        <div key={keyValue} className={`${className} page-enter`.trim()}>
            {children}
        </div>
    );
}
