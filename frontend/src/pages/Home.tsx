import { MapContainer, Marker, Popup, TileLayer } from "react-leaflet";
import 'leaflet/dist/leaflet.css';

export default function Home() {
    return (
        <MapContainer className="h-full w-screen" center={[46.5, 2.5]} zoom={6} minZoom={3} maxZoom={18} maxBounds={[[-85, -180],
        [85, 180],
        ]} scrollWheelZoom={true} maxBoundsViscosity={1.0}
            worldCopyJump={true}
        >
            <TileLayer
                attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
            />
            <Marker position={[48.6938980416762, 6.181735504415509]}>
                <Popup>
                    Nancy <br /> Chez nous.
                </Popup>
            </Marker>
        </MapContainer>
    )
}