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
        }
        
        currentUserLocation = currentLocation
    }
    
}
