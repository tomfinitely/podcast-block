/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps, InnerBlocks } from '@wordpress/block-editor';

/**
 * The save function defines the way in which the different attributes should
 * be combined into the final markup, which is then serialized by the block
 * editor into `post_content`.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#save
 *
 * @param {Object} props - Block props
 * @param {Object} props.attributes - Block attributes
 * @return {Element} Element to render.
 */
export default function save({ attributes }) {
	const { platformLinks } = attributes;
	
	const blockProps = useBlockProps.save({
		className: 'podcast-block-container'
	});

	// Platform icons mapping
	const platformIcons = {
		spotify: '🎵',
		apple: '🍎',
		acast: '🅰️',
		castos: '🎙️',
		libsyn: '🧩',
		transistor: '⚡',
		pocketcasts: '📱',
		rss: '📡'
	};

	// Platform options for labels
	const platformOptions = [
		{ label: 'Spotify', value: 'spotify' },
		{ label: 'Apple Podcasts', value: 'apple' },
		{ label: 'Acast', value: 'acast' },
		{ label: 'Castos', value: 'castos' },
		{ label: 'Libsyn', value: 'libsyn' },
		{ label: 'Transistor', value: 'transistor' },
		{ label: 'Pocket Casts', value: 'pocketcasts' },
		{ label: 'RSS Feed', value: 'rss' }
	];

	return (
		<div {...blockProps}>
			<div className="podcast-block-content">
				{platformLinks && platformLinks.length > 0 && (
					<div className="podcast-block-platform-links">
						<h4>Listen on:</h4>
						<div className="platform-links-grid">
							{platformLinks.map((link, index) => (
								<a
									key={index}
									href={link.url}
									target="_blank"
									rel="noopener noreferrer"
									className="platform-link"
								>
									<span className="platform-icon">{platformIcons[link.platform]}</span>
									<span className="platform-label">
										{link.label || platformOptions.find(opt => opt.value === link.platform)?.label}
									</span>
								</a>
							))}
						</div>
					</div>
				)}
				<InnerBlocks.Content />
			</div>
		</div>
	);
}
