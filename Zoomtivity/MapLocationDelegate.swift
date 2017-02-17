//
//  MapLocationDelegate.swift
//  Zoomtivity
//
//  Created by Callum Trounce on 17/02/2017.
//  Copyright © 2017 Zoomtivity. All rights reserved.
//

import Foundation
import SKMaps

extension MapViewController : SKPositionerServiceDelegate {
    
    
    
    func positionerService(_ positionerService: SKPositionerService!, updatedCurrentLocation currentLocation: CLLocation!) {
        
        if currentUserLocation == nil {
            var region = SKCoordinateRegion()
            region.center = CLLocationCoordinate2DMake(currentLocation.coordinate.latitude,
                                                       currentLocation.coordinate.longitude);
            region.zoomLevel = 17;
            mapView.visibleRegion = region;
            
            let northEastCoords = mapView.coordinate(for: CGPoint.init(x: mapView.bounds.size.width - 1, y: 1))
            let southWestCoords = mapView.coordinate(for: CGPoint.init(x: 1, y: mapView.bounds.size.height - 1))
            DatabaseManager.sharedDataManager.fetchPoints(type: "food",
                                                          southWestPoint: southWestCoords,
                                                          northEastPoint: northEastCoords)
            
            
        }
        
        currentUserLocation = currentLocation
    }
    
}
