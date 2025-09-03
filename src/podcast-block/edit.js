/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps, InnerBlocks, InspectorControls, useInnerBlocksProps } from '@wordpress/block-editor';

/**
 * WordPress components
 */
import { 
	PanelBody, 
	TextControl, 
	SelectControl, 
	Button, 
	Spinner, 
	Notice,
	Placeholder,
	RangeControl,
	ToggleControl,
	Card,
	CardBody,
	Flex,
	FlexItem
} from '@wordpress/components';

/**
 * WordPress hooks
 */
import { useState, useEffect } from '@wordpress/element';
import { createBlock } from '@wordpress/blocks';
import { useDispatch } from '@wordpress/data';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @param {Object} props - Block props
 * @param {Object} props.attributes - Block attributes
 * @param {Function} props.setAttributes - Function to update attributes
 * @return {Element} Element to render.
 */
export default function Edit({ attributes, setAttributes, clientId }) {
	const { profileUrl, platform, quantity, platformLinks, isLoading, error } = attributes;
	const [localUrl, setLocalUrl] = useState(profileUrl);
	const [showPlatformLinks, setShowPlatformLinks] = useState(platformLinks.length > 0);
	const { replaceInnerBlocks } = useDispatch('core/block-editor');

	// Platform options
	const platformOptions = [
		{ label: 'Spotify', value: 'spotify' },
		{ label: 'Overcast', value: 'overcast' },
		{ label: 'Apple Podcasts', value: 'apple' },
		{ label: 'Acast', value: 'acast' },
		{ label: 'Castos', value: 'castos' },
		{ label: 'Libsyn', value: 'libsyn' },
		{ label: 'Transistor', value: 'transistor' },
		{ label: 'Pocket Casts', value: 'pocketcasts' },
		{ label: 'RSS Feed', value: 'rss' }
	];

	// Platform icons mapping
	const platformIcons = {
		spotify: '🎵',
		overcast: '☁️',
		apple: '🍎',
		acast: '🅰️',
		castos: '🎙️',
		libsyn: '🧩',
		transistor: '⚡',
		pocketcasts: '📱',
		rss: '📡'
	};

	// Add platform link
	const addPlatformLink = () => {
		const newLinks = [...platformLinks, { platform: 'spotify', url: '', label: '' }];
		setAttributes({ platformLinks: newLinks });
	};

	// Update platform link
	const updatePlatformLink = (index, field, value) => {
		const newLinks = [...platformLinks];
		newLinks[index] = { ...newLinks[index], [field]: value };
		setAttributes({ platformLinks: newLinks });
	};

	// Remove platform link
	const removePlatformLink = (index) => {
		const newLinks = platformLinks.filter((_, i) => i !== index);
		setAttributes({ platformLinks: newLinks });
	};

	// Fetch podcasts from profile URL
	const fetchPodcasts = async () => {
		if (!localUrl.trim()) {
			setAttributes({ error: __('Please enter a valid profile URL', 'podcast-block') });
			return;
		}

		setAttributes({ isLoading: true, error: '' });

		try {
			// Create a REST API endpoint call to fetch podcasts
			const response = await fetch('/wp-json/podcast-block/v1/fetch-podcasts', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': wpApiSettings.nonce
				},
				body: JSON.stringify({
					url: localUrl,
					platform: platform
				})
			});

			if (!response.ok) {
				throw new Error(__('Failed to fetch podcasts', 'podcast-block'));
			}

			const data = await response.json();
			
			if (data.success && data.podcasts) {
				// Limit podcasts to the specified quantity
				const limitedPodcasts = data.podcasts.slice(0, quantity);
				
				// Create audio blocks for each podcast
				const audioBlocks = limitedPodcasts.map(podcast => 
					createBlock('core/audio', {
						src: podcast.audio_url,
						caption: podcast.title
					})
				);

				// Replace inner blocks with new audio blocks
				replaceInnerBlocks(clientId, audioBlocks);
				
				setAttributes({ 
					profileUrl: localUrl,
					isLoading: false,
					error: ''
				});
			} else {
				throw new Error(data.message || __('No podcasts found', 'podcast-block'));
			}
		} catch (err) {
			setAttributes({ 
				isLoading: false, 
				error: err.message || __('Error fetching podcasts', 'podcast-block')
			});
		}
	};

	// Update local URL when profileUrl changes
	useEffect(() => {
		setLocalUrl(profileUrl);
	}, [profileUrl]);

	const blockProps = useBlockProps({
		className: 'podcast-block-container'
	});

	// Template for InnerBlocks (Audio blocks)
	const template = [
		['core/audio', {}]
	];

	return (
		<div {...blockProps}>
			<InspectorControls>
				<PanelBody title={__('Podcast Settings', 'podcast-block')}>
					<SelectControl
						label={__('Platform', 'podcast-block')}
						value={platform}
						options={platformOptions}
						onChange={(value) => setAttributes({ platform: value })}
					/>
					<TextControl
						label={__('Profile URL', 'podcast-block')}
						value={localUrl}
						onChange={setLocalUrl}
						placeholder={__('Enter podcast profile URL...', 'podcast-block')}
						help={__('Enter the URL of the podcast profile page (e.g., Spotify artist page, Overcast profile, etc.)', 'podcast-block')}
					/>
					<RangeControl
						label={__('Number of Podcasts', 'podcast-block')}
						value={quantity}
						onChange={(value) => setAttributes({ quantity: value })}
						min={1}
						max={20}
						help={__('Set how many podcast episodes to display', 'podcast-block')}
					/>
					<Button
						variant="primary"
						onClick={fetchPodcasts}
						disabled={isLoading || !localUrl.trim()}
						isBusy={isLoading}
					>
						{isLoading ? __('Fetching...', 'podcast-block') : __('Fetch Podcasts', 'podcast-block')}
					</Button>
				</PanelBody>
				
				<PanelBody title={__('Platform Links', 'podcast-block')} initialOpen={false}>
					<ToggleControl
						label={__('Show Platform Links', 'podcast-block')}
						checked={showPlatformLinks}
						onChange={(value) => {
							setShowPlatformLinks(value);
							if (!value) {
								setAttributes({ platformLinks: [] });
							}
						}}
						help={__('Display platform links above the podcast list', 'podcast-block')}
					/>
					
					{showPlatformLinks && (
						<div className="platform-links-controls">
							{platformLinks.map((link, index) => (
								<Card key={index} className="platform-link-card">
									<CardBody>
										<Flex justify="space-between" align="center">
											<FlexItem>
												<strong>{__('Platform Link', 'podcast-block')} {index + 1}</strong>
											</FlexItem>
											<FlexItem>
												<Button
													variant="secondary"
													size="small"
													onClick={() => removePlatformLink(index)}
													isDestructive
												>
													{__('Remove', 'podcast-block')}
												</Button>
											</FlexItem>
										</Flex>
										<SelectControl
											label={__('Platform', 'podcast-block')}
											value={link.platform}
											options={platformOptions}
											onChange={(value) => updatePlatformLink(index, 'platform', value)}
										/>
										<TextControl
											label={__('URL', 'podcast-block')}
											value={link.url}
											onChange={(value) => updatePlatformLink(index, 'url', value)}
											placeholder={__('Enter platform URL...', 'podcast-block')}
										/>
										<TextControl
											label={__('Label (Optional)', 'podcast-block')}
											value={link.label}
											onChange={(value) => updatePlatformLink(index, 'label', value)}
											placeholder={__('Custom label for this link', 'podcast-block')}
										/>
									</CardBody>
								</Card>
							))}
							<Button
								variant="secondary"
								onClick={addPlatformLink}
							>
								{__('Add Platform Link', 'podcast-block')}
							</Button>
						</div>
					)}
				</PanelBody>
			</InspectorControls>

			<div className="podcast-block-editor">
				{!profileUrl ? (
					<Placeholder
						icon="microphone"
						label={__('Podcast Block', 'podcast-block')}
						instructions={__('Enter a podcast profile URL and select the platform to automatically populate with podcast episodes.', 'podcast-block')}
					>
						<div className="podcast-block-placeholder-controls">
							<SelectControl
								label={__('Platform', 'podcast-block')}
								value={platform}
								options={platformOptions}
								onChange={(value) => setAttributes({ platform: value })}
							/>
							<TextControl
								label={__('Profile URL', 'podcast-block')}
								value={localUrl}
								onChange={setLocalUrl}
								placeholder={__('Enter podcast profile URL...', 'podcast-block')}
							/>
							<RangeControl
								label={__('Number of Podcasts', 'podcast-block')}
								value={quantity}
								onChange={(value) => setAttributes({ quantity: value })}
								min={1}
								max={20}
							/>
							<Button
								variant="primary"
								onClick={fetchPodcasts}
								disabled={isLoading || !localUrl.trim()}
								isBusy={isLoading}
							>
								{isLoading ? __('Fetching...', 'podcast-block') : __('Fetch Podcasts', 'podcast-block')}
							</Button>
						</div>
					</Placeholder>
				) : (
					<div className="podcast-block-content">
						{showPlatformLinks && platformLinks.length > 0 && (
							<div className="podcast-block-platform-links">
								<h4>{__('Listen on:', 'podcast-block')}</h4>
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
						
						<div className="podcast-block-header">
							<h3>{__('Podcast Episodes', 'podcast-block')}</h3>
							<div className="podcast-block-meta">
								<span className="platform-badge">{platform}</span>
								<span className="quantity-badge">{quantity} {__('episodes', 'podcast-block')}</span>
								<Button
									variant="secondary"
									size="small"
									onClick={fetchPodcasts}
									disabled={isLoading}
									isBusy={isLoading}
								>
									{__('Refresh', 'podcast-block')}
								</Button>
							</div>
						</div>

						{error && (
							<Notice status="error" isDismissible={false}>
								{error}
							</Notice>
						)}

						{isLoading && (
							<div className="podcast-block-loading">
								<Spinner />
								<span>{__('Fetching podcasts...', 'podcast-block')}</span>
							</div>
						)}

						<div className="podcast-block-inner-blocks">
							<InnerBlocks
								template={template}
								templateLock={false}
								allowedBlocks={['core/audio', 'core/paragraph', 'core/heading']}
							/>
						</div>
					</div>
				)}
			</div>
		</div>
	);
}
